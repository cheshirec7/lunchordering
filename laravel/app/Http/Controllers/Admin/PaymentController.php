<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Payment;
use App\Repositories\AccountRepository;
use App\Repositories\PaymentRepository;
use Datatables;
use Illuminate\Http\Request;
use Validator;

class PaymentController extends Controller
{
    protected $payments;
    protected $accounts;

    /**
     * Create a new controller instance.
     *
     * @param  PaymentRepository $payments
     * @param AccountRepository $accounts
     */
    public function __construct(PaymentRepository $payments, AccountRepository $accounts)
    {
        $this->payments = $payments;
        $this->accounts = $accounts;
    }

    /*****/
    public function index(Request $request)
    {
        return view('admin.payments.index', ['accounts' => $this->accounts->activeAccounts()]);
    }

    /*****/
    public function show($id)
    {
        $account_id = intval($id);

        if ($account_id == 0) {
            $output = array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            );
            return response()->json($output);
        }

        $payments = $this->payments->datatableByAccount($account_id);
        return Datatables::of($payments)
            ->edit_column('credit_amt', '${{ number_format($credit_amt/100,2) }}')
            ->edit_column('pay_method', '@if($pay_method==1) Cash @elseif($pay_method==2) Check @elseif($pay_method==3) PayPal @else Adjustment @endif')
            ->add_column('pay_method_int', '{{ $pay_method }}')
            ->make();
    }

    /*****/
    public function store(Request $request)
    {
        $inputs = $request->only('pay_id', 'account_id', 'pay_method', 'credit_desc', 'credit_date', 'credit_amt');
        $credit_date = date_create($inputs['credit_date']);
        $msg = '';
        if (!$credit_date) {
            $e = date_get_last_errors();
            foreach ($e['errors'] as $error) {
                $msg .= $error . ' ';
            }
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => 'Date error: ' . $msg));
        }

        $credit_amt = floatval($inputs['credit_amt']);
        if ($credit_amt == 0)
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => 'Amount cannot be zero.'));

        $inputs['credit_desc'] = filter_var($inputs['credit_desc'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $pay_id = intval($inputs['pay_id']);
        $account_id = intval($inputs['account_id']);

        $rules = array(
            'account_id' => array('required', 'integer', 'min:1'),
            'pay_method' => array('required', 'integer', 'min:1', 'max:4'),
        );

        if ($pay_id > 0) {
            $payment = Payment::find($pay_id);
            if (!$payment)
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to edit. The payment no longer exists.'));
        } else
            $payment = new Payment();

        $validator = Validator::make($inputs, $rules);
        if ($validator->passes()) {
            $payment->account_id = $account_id;
            $payment->pay_method = $inputs['pay_method'];
            $payment->credit_desc = $inputs['credit_desc'];
            $payment->credit_date = $credit_date;
            $payment->credit_amt = $credit_amt * 100;

            if ($payment->save()) {
                $this->accounts->updateAccountCredits($account_id);//when paypal must recalc fees, but currently not an option from this form!
                //$bal = $this->accounts->currentBalance($account_id); 'bal' => $bal)
                return response()->json(array('error' => false, 'idToFind' => $payment->id));
            } else
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to save payment.'));
        } else
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => $validator->messages()->first()));
    }

    /*****/
    public function destroy($id)
    {
        $pay_id = intval($id);

        $payment = Payment::find($pay_id);

        if (!$payment)
            return response()->json(array('error' => false));

        try {
            Payment::destroy($pay_id);
            $this->accounts->updateAccountCredits($payment->account_id);
            //$bal = $this->accounts->currentBalance($payment->account_id); , 'bal' => $bal
            return response()->json(array('error' => false));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            if ($e->getCode() == 23000) {
                return response()->json(array('error' => true, 'msg' => 'Unable to delete payment'));
            } else {
                return response()->json(array('error' => true, 'msg' => $e->getMessage()));
            }
        }
    }
}
