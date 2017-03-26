<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\LunchDate;
use App\Order;
use App\Repositories\LunchDateRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use Datatables;
use DB;
use Illuminate\Http\Request;
use Validator;

class OrderMaintController extends Controller
{
    protected $orders;
    protected $lunchdates;
    protected $users;

    /**
     * Create a new controller instance.
     *
     * @param  OrderRepository $orders
     * @param  LunchDateRepository $lunchdates
     */
    public function __construct(OrderRepository $orders, LunchDateRepository $lunchdates, UserRepository $users)
    {
        $this->orders = $orders;
        $this->lunchdates = $lunchdates;
        $this->users = $users;
    }

    /*****/
    public function index(Request $request)
    {
        return view('admin.ordermaint.index', ['dates' => $this->lunchdates->datesWithOrders()]);
    }

    /*****/
    public function show($id)
    {
        $lunchdate_id = intval($id);
        if ($lunchdate_id == 0) {
            $output = array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            );
            return response()->json($output);
        }

        $orders = $this->orders->datatableForLunchdateID($lunchdate_id);

        return Datatables::of($orders)
            //->edit_column('teacher_name_grade', '@if($teacher_id==1) (n/a) @else {{ $teacher_name_grade }} @endif')
            ->edit_column('grade_desc', '@if($grade_id==1) (n/a) @else {{ $grade_desc }} @endif')
            ->edit_column('total_price', '${{ number_format($total_price/100,2) }}')
            ->remove_column('teacher_id')
            //->remove_column('grade_id')
            ->make();
    }

    /*****/
    public function getTransferNames(Request $request)
    {
        $inputs = $request->only('user_id', 'lunchdate_id');
        $user_id = intval($inputs['user_id']);
        $lunchdate_id = intval($inputs['lunchdate_id']);
        $data = $this->users->getTransferNames($lunchdate_id, $user_id);

        return response()->json($data);
    }


    /*****/
    public function postTransfer(Request $request)
    {

    }

    /*****/
    public function postLunchDateLockToggle(Request $request, $lunchdate_id)
    {
        $ld = LunchDate::find($lunchdate_id);
        if ($ld) {
            $locked = 0;
            if ($ld->orders_placed) { //not null
                $ld->orders_placed = NULL;
            } else {
                $ld->orders_placed = date_create();
                $locked = 1;
            }
            if ($ld->save()) {
                $affected = DB::update('update los_orders set status_code=1 where status_code=0 and order_date in (SELECT provide_date FROM los_lunchdates WHERE orders_placed is not null)');
                $affected = DB::update('update los_orders set status_code=0 where status_code=1 and order_date in (SELECT provide_date FROM los_lunchdates WHERE orders_placed is null)');
                return response()->json(array('error' => false, 'locked' => $locked));
            } else
                return response()->json(array('error' => true, 'msg' => 'Unable to update lunch date.'));
        } else
            return response()->json(array('error' => true, 'msg' => 'Lunch date not found.'));
    }

    /*****/
    public function store(Request $request)
    {
        $inputs = $request->only('order_id', 'total_price', 'notes');
        $total_price = floatval($inputs['total_price']);
        if ($total_price < 0)
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => 'Amount cannot be less than zero.'));

        $inputs['notes'] = filter_var($inputs['notes'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $order_id = intval($inputs['order_id']);

        $rules = array(
            'order_id' => array('required', 'integer', 'min:1')
        );

        if ($order_id > 0) {

            $order = Order::find($order_id);
            if (is_null($order)) {
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to edit. The order no longer exists.'));
            }
        } else
            $order = new Order();

        $validator = Validator::make($inputs, $rules);
        if ($validator->passes()) {
            $order->total_price = $total_price * 100;
            $order->notes = $inputs['notes'];

            if ($order->save()) {
                return response()->json(array('error' => false, 'idToFind' => $order->id));
            } else
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to save order.'));
        } else
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => $validator->messages()->first()));
    }

    /*****/
    public function destroy($id)
    {
//        $pay_id = intval($id);
//
//        $payment = Payment::find($pay_id);
//
//        if (!$payment)
//            return response()->json(array('error' => false));
//
//        try {
//            Payment::destroy($pay_id);
//            $this->accounts->updateAccountCredits($payment->account_id);
//            $bal = $this->accounts->currentBalance($payment->account_id);
//            return response()->json(array('error' => false, 'bal' => $bal));
//        } catch (\Exception $e) {
//            \Log::error($e->getMessage());
//            if ($e->getCode() == 23000) {
//                return response()->json(array('error' => true, 'msg' => 'Unable to delete payment'));
//            } else {
//                return response()->json(array('error' => true, 'msg' => $e->getMessage()));
//            }
//        }
    }
}
