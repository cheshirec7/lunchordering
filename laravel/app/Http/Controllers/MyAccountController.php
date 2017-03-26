<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Payment;
use App\Repositories\AccountRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\UserRepository;
use Auth;
use Datatables;
use Illuminate\Http\Request;

class MyAccountController extends Controller
{
    protected $accounts;
    protected $users;
    protected $orders;
    protected $payments;

    /**
     * Create a new controller instance.
     *
     * @param AccountRepository $accounts
     * @param UserRepository $users
     * @param OrderRepository $orders
     * @param PaymentRepository $payments
     */
    public function __construct(AccountRepository $accounts,
                                UserRepository $users,
                                OrderRepository $orders,
                                PaymentRepository $payments)
    {
        $this->middleware('auth');
        $this->accounts = $accounts;
        $this->users = $users;
        $this->orders = $orders;
        $this->payments = $payments;
    }

    /*****/
    public function index(Request $request)
    {
        $this->accounts->updateCreditsDebitsFeesOrderCount(Auth::id());

        //order_count,total_price,first_name,last_name,user_id
        $order_aggs = $this->orders->myAccountAggregates(Auth::id());

        //payment_count,credit_amt
        $payment_agg = $this->payments->myAccountAggregate(Auth::id());

        $fees_paid = 0;
        $fees = $this->payments->myAccountFees(Auth::id());
        if ($fees)
            $fees_paid = $fees->fees;

        $cur_balance = $this->accounts->currentBalance(Auth::id());

        $trx_fee = '';
        $total_due = '';
        if ($cur_balance < 0) {
            $trx_fee = round(-$cur_balance * config('app.paypay_pct'));
            $total_due = -$cur_balance + $trx_fee;
        }

        return view('myaccount.index', [
            'order_aggs' => $order_aggs,
            'payment_agg' => $payment_agg,
            'fees_paid' => $fees_paid,
            'cur_balance' => $cur_balance,
            'trx_fee' => $trx_fee,
            'total_due' => $total_due
        ]);
    }

    /*****/
    public function orders($user_id)
    {
        $user_orders = $this->orders->datatableForUserID($user_id);
        return Datatables::of($user_orders)
            ->edit_column('total_price', '${!! number_format($total_price/100,2) !!}')
            ->make();
    }

    /*****/
    public function payments()
    {
        $account_payments = $this->payments->datatableMyAccountPayments();
        return Datatables::of($account_payments)
            ->edit_column('credit_amt', '${!! number_format($credit_amt/100,2) !!}')
            ->edit_column('pay_method', '@if($pay_method==1) Cash @elseif($pay_method==2) Check @elseif($pay_method==3) PayPal @elseif($pay_method==4) Adjustment @else Error @endif')
            ->make();
    }

    /*****/
    public function pay(Request $request)
    {
        $inputs = $request->only('credit_amt', 'fee');
        $paymentAmount = floatval($inputs['credit_amt'] / 100);
        //$fee = floatval($inputs['fee']/100);
        if ($paymentAmount <= 0) {
            return redirect('myaccount')->with('status', 'Payment amount must be greater than zero.');
        }

        $currencyCodeType = 'USD';
        $paymentType = 'Sale';
        $returnURL = urlencode(url('myaccount/completepayment'));
        $cancelURL = urlencode(url('myaccount'));
        $items_str = "&L_NAME0=CCA%20Lunch%20Order&L_AMT0=" . $paymentAmount . "&L_QTY0=1";

        $nvpstr = "&NOSHIPPING=1&ALLOWNOTE=0&AMT=" . $paymentAmount . "&PAYMENTACTION=" . $paymentType;
        $nvpstr .= "&RETURNURL=" . $returnURL . "&CANCELURL=" . $cancelURL . "&CURRENCYCODE=" . $currencyCodeType . "&LOCALECODE=US" . $items_str;

        $resArray = $this->hash_call($request, "SetExpressCheckout", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == config('app.PAYPAL_ACK_SUCCESS')) {
            $request->session()->put('amtToPay', $inputs['credit_amt']);
            $request->session()->put('fee', $inputs['fee']);
            $token = urldecode($resArray["TOKEN"]);
            $payPalURL = config('app.PAYPAL_URL') . $token;
            return redirect($payPalURL);
        } else {
            return redirect('myaccount')->with('status', $ack);
        }
    }

    /*****/

    private function hash_call(Request $request, $methodName, $nvpStr)
    {
        $nvpheader = $this->nvpHeader();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('app.PAYPAL_API_ENDPOINT'));
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //TODO TRUE
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $nvpStr = $nvpheader . $nvpStr;
        //check if version is included in $nvpStr else include the version.
        if (strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
            $nvpStr = "&VERSION=" . urlencode(config('app.PAYPAL_VERSION')) . $nvpStr;
        }
        $nvpreq = "METHOD=" . urlencode($methodName) . $nvpStr;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
        $response = curl_exec($ch);
        $nvpResArray = $this->deformatNVP($response);
        if (curl_errno($ch))
            $request->session()->flash('error', curl_error($ch));
        curl_close($ch);
        if (isset($nvpResArray["L_LONGMESSAGE0"]))
            $request->session()->flash('error', $nvpResArray["L_LONGMESSAGE0"]);
        return $nvpResArray;
    }

    /*****/

    private function nvpHeader()
    {
        $nvpHeaderStr = "&PWD=" . urlencode(config('app.PAYPAL_API_PASSWORD')) .
            "&USER=" . urlencode(config('app.PAYPAL_API_USERNAME')) .
            "&SIGNATURE=" . urlencode(config('app.PAYPAL_API_SIGNATURE'));
        return $nvpHeaderStr;
    }

    /*****/

    private function deformatNVP($nvpstr)
    {
        $intial = 0;
        $nvpArray = array();
        while (strlen($nvpstr)) {
            $keypos = strpos($nvpstr, '=');
            $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }
        return $nvpArray;
    }

    public function completePayment(Request $request)
    {
        $inputs = $request->only('token', 'PayerID');
        $token = urlencode($inputs['token']);
        $nvpstr = "&TOKEN=" . $token;
        $resArray = $this->hash_call($request, "GetExpressCheckoutDetails", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {

            if ($request->session()->has('amtToPay')) {
                $paymentAmount = $request->session()->get('amtToPay') / 100;
            } else {
                \Log::error('PayPal: Amount to pay was lost.');
                return redirect('myaccount')->with('status', 'Amount to pay was lost. Please try again.');
            }

            $currencyCodeType = 'USD';
            $paymentType = 'Sale';
            $payerID = urlencode($inputs['PayerID']);
            $serverName = urlencode($_SERVER['SERVER_NAME']);
            $nvpstr = '&TOKEN=' . $token . '&PAYERID=' . $payerID . '&PAYMENTACTION=' . $paymentType . '&AMT=' . $paymentAmount . '&CURRENCYCODE=' . $currencyCodeType . '&IPADDRESS=' . $serverName;
            $resArray = $this->hash_call($request, "DoExpressCheckoutPayment", $nvpstr);
            $ack = strtoupper($resArray["ACK"]);
            if ($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING') {
                \Log::error($resArray);
                return redirect('myaccount')->with('status', $ack);
            } else {
                $payment = new Payment();
                $payment->account_id = Auth::id();
                $payment->pay_method = config('app.pay_method_paypal');
                $payment->credit_desc = $resArray['TRANSACTIONID'];
                $payment->credit_date = new \DateTime();;
                $payment->credit_amt = $paymentAmount * 100;
                $payment->fee = $request->session()->get('fee');

                if ($payment->save()) {
                    $this->accounts->updateCreditsDebitsFeesOrderCount(Auth::id());
                    return redirect('myaccount')->with('success', 'Payment complete. Thank you!');
                } else
                    return redirect('myaccount')->with('error', 'There was a problem saving.');
            }
        } else {
            \Log::error($resArray);
            return redirect('myaccount')->with('status', 'Something went wrong. Please try again.');
        }
    }

}
