<?php

namespace App\Http\Controllers;

use App\Account;
use App\LunchDate;
use App\LunchDateMenuItem;
use App\MenuItem;
use App\NoLunchException;
use App\Order;
use App\OrderDetail;
use App\Repositories\AccountRepository;
use App\Repositories\OrderRepository;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @var OrderRepository $orders
     * @var AccountRepository $accounts
     */
    protected $orders;
    protected $accounts;

    /**
     * Create a new controller instance.
     *
     * @param  OrderRepository $orders
     * @param  AccountRepository $accounts
     */
    public function __construct(OrderRepository $orders, AccountRepository $accounts)
    {
        $this->middleware('auth');
        $this->orders = $orders;
        $this->accounts = $accounts;
    }

    /**
     * Display an accounts' orders.
     *
     * @param  Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $req = new Request();
        $req["period"] = 0;
        $req["displayPeriod"] = "week";

        $amountDue = '';
        $accounts = array();
        $isUser = Auth::user()->privilege_level < config('app.privilege_level_admin');

        if ($isUser) {
            $amountDue = $this->accounts->amountDue(Auth::id(), false);
        } else {
            $accounts = Account::select('id', 'account_name')
                ->where('enabled', '=', 1)
                ->orderBy('account_name')
                ->get();
        }

        return view('orders.index', ['lunchestableweek' => $this->show(Auth::id(), $req), 'accounts' => $accounts, 'accid' => Auth::id(), 'isUser' => $isUser, 'amountDue' => $amountDue]);
    }

    public function show($id, Request $request)
    {
        $account_id = intval($id); //check if not admin and wrong acct

        $today = Carbon::now()->setTime(0, 0, 0);
        $todayYMD = $today->toDateString();
        $todayTimestamp = $today->getTimestamp();//strtotime($todayYMD);

        if ($today->dayOfWeek === Carbon::SATURDAY) {
            $start = $today->addDays(2);
        } else if ($today->dayOfWeek === Carbon::SUNDAY) {
            $start = $today->addDays(1);
        } else
            $start = $today->startOfWeek();
        $start->addDays($request->period * 7); //monday
        $end = $start->copy()->addDays(4); //friday

        $lunchdates = LunchDate::select('los_lunchdates.id AS lunchdate_id', 'lp.id AS prov_id', 'provider_name', 'provider_image',
            'provide_date', 'allow_orders', 'orders_placed', 'additional_text', 'extended_care_text', 'provider_url')
            ->join('los_providers as lp', 'lp.id', '=', 'los_lunchdates.provider_id')
            ->whereBetween('provide_date', array($start, $end))
            ->orderBy('provide_date')
            ->get();

        $nles = NoLunchException::select('id', 'exception_date', 'reason', 'description', 'grade_id', 'teacher_id')
            ->whereBetween('exception_date', array($start, $end))
            ->orderBy('exception_date')
            ->get();

        $orders = Order::select('id AS order_id', 'user_id', 'short_desc', 'order_date', 'total_price', 'status_code')
            ->whereBetween('order_date', array($start, $end))
            ->orderBy('order_date')
            ->get();

        $users = User::select('id', 'first_name', 'last_name', 'allowed_to_order', 'teacher_id', 'grade_id', 'user_type')
            ->where('account_id', '=', $account_id)
            ->orderBy('last_name', 'first_name')
            ->get();

        $account = Account::select('enabled', 'privilege_level', 'no_new_orders')
            ->where('id', '=', $account_id)
            ->first();

        $providers_row = '';
        $lunchdates_row = '';
        $providerIDs = array(0, 0, 0, 0, 0);
        $res = '';
        $loopDate = $start->copy();

        //loop mon-fri
        for ($i = 0; $i < 5; $i++) {
            $loopDateYMD = $loopDate->toDateString();

            $providers_row .= '<th>';
            foreach ($lunchdates as $lunchdate) {
                if (date("Y-m-d", strtotime($lunchdate->provide_date)) == $loopDateYMD) {
                    $providers_row .= '<a data-provid="' . $lunchdate->prov_id . '" target="_blank" href="' . $lunchdate->provider_url . '">';
                    $providers_row .= '<img src="/img/providers/' . $lunchdate->provider_image . '" alt="' . $lunchdate->provider_name . '" title="' . $lunchdate->provider_name . '"></a>';
                    $providerIDs[$i] = $lunchdate->prov_id;
                    break;
                }
            }

            if ($providerIDs[$i] == 0)
                $providers_row .= '<img src="/img/providers/nolunches.png" alt="No Lunches Scheduled" title="No Lunches Scheduled">';
            $providers_row .= '</th>';

            if ($loopDateYMD == $todayYMD)
                $lunchdates_row .= '<th class="today">' . $loopDate->format("D M j") . '</th>';
            else
                $lunchdates_row .= '<th>' . $loopDate->format("D M j") . '</th>';
            $loopDate->addDay();
        }

        $res .= '<tr class="providers"><th class="usercol"></th>' . $providers_row . '</tr>';
        $res .= '<tr class="lunchdates"><th class="usercol">Name</th>' . $lunchdates_row . '</tr>';

        foreach ($users as $user) {
            $res .= '<tr><th colspan="6" class="userrow"><div class="username">' . $user->first_name . ' ' . $user->last_name . '</div></th></tr>';
            $res .= '<tr>';
            $data = 'data-username="' . $user->first_name . ' ' . $user->last_name . '" data-userid="' . $user->id . '"';
            $res .= '<td class="usercol" ' . $data . '>' . $user->first_name . '<br />' . $user->last_name . '</div></td>';

            $loopDate = $start->copy();
            for ($i = 0; $i < 5; $i++) {
                $loopDateYMD = $loopDate->toDateString();
                $res .= $this->getOrderCellHTML($lunchdates, $nles, $user, $orders, $todayYMD, $todayTimestamp, $loopDateYMD, $account);
                $loopDate->addDay();
            }
            $res .= '</tr>';
        }

        if (count($users) == 1)
            $res .= '<tr><td class="usercol"></td><td class="spacer"></td><td></td><td></td><td></td><td></td></tr>';

        return '<table class="lunchestable table-bordered">' . $res . '<tr><td colspan="6" class="emptyrow">&nbsp;</td></tr></table>';
    }

    private function getOrderCellHTML($lunchdates, $nles, $user, $orders, $todayYMD, $todayTimestamp, $curDateYMD, $account)
    {
        $curTimestamp = strtotime($curDateYMD);
        $haveLunchDate = false;
        $allowOrders = false;
        $ordersPlaced = false;
        $addltxt = null;
        $extcare = null;
        $nleReason = null;
        $nleDesc = null;
        $orderLunchText = null;
        $orderPrice = null;
        $lunchdate_id = 0;
        //$orderID = 0;

        foreach ($lunchdates as $lunchdate) { //loop thru all lunch dates; must be defined for anything to show on schedule

            if (date("Y-m-d", strtotime($lunchdate->provide_date)) == $curDateYMD) {
                $haveLunchDate = true;
                $lunchdate_id = $lunchdate->lunchdate_id;
                $addltxt = $lunchdate->additional_text;
                $extcare = $lunchdate->extended_care_text;
                $allowOrders = $lunchdate->allow_orders > 0;
                $ordersPlaced = !empty($lunchdate->orders_placed);
                break;
            }
        }

        if (!$haveLunchDate)
            return '<td class="spacer"></td>';

        foreach ($nles as $nle) { //check for exception
            if (date("Y-m-d", strtotime($nle->exception_date)) == $curDateYMD) {
//                if (($user->teacher_id == $nle->teacher_id) || ($user->grade_id == $nle->grade_id)) {
                if ($user->grade_id == $nle->grade_id) {
                    $nleReason = $nle->reason;
                    $nleDesc = $nle->description;
                    break;
                }
            }
        }

        if (empty($nleReason)) { //check for order
            foreach ($orders as $order) {
                if ((date("Y-m-d", strtotime($order->order_date)) == $curDateYMD) && ($user->id == $order->user_id)) {
                    //$orderID = $order->order_id;
                    $orderLunchText = $order->short_desc;
                    //$orderPrice = '$'.number_format($order->totalPrice/100,2);
                    break;
                }
            }
        }

        $body = '';
        $editable = false;
        $data = 'data-lunchdateid="' . $lunchdate_id . '" data-ts="' . $curTimestamp . '" data-userid="' . $user->id . '"';

        if (!empty($nleReason)) {
            $body .= '<div class="nlereason">' . $nleReason . '</div>';
            if (!empty($nleDesc))
                $body .= '<div class="nledesc">' . $nleDesc . '</div>';
        } else if (!empty($orderLunchText)) {
            $lt = '<div class="lunchtext">' . $orderLunchText . '</div>';
            //$lt .= '<div class="price">'.$orderPrice.'</div>';
            $editable = ($curTimestamp > $todayTimestamp && !$ordersPlaced);
            if ($editable)
//                $lt = '<div class="clickablearea"'.$data.'>'.$lt.'<div class="glyphicon glyphicon-edit"></div>&nbsp;&nbsp;<div class="glyphicon glyphicon-remove"></div></div>';
                $lt = '<button class="clickablearea"' . $data . '>' . $lt;// . '<div class="fa fa-edit"></div>';
            $body .= $lt;

        } else if ($curTimestamp > $todayTimestamp && !$ordersPlaced && $allowOrders) {

            if ($user->allowed_to_order && $account->no_new_orders == 0 && $account->enabled == 1) {
                $body .= '<button class="clickablearea"' . $data . '>';
                $body .= '<div class="fa fa-plus-circle"></div>';
                $body .= '<div class="ordertext">Order</div>';
                //$body .= '</button>';
                $editable = true;
            } else {
                $body .= '<div class="nlo">Lunch<br />Ordering<br />Disabled</div>';
            }

        } else if ($allowOrders) {
            $body .= '<div class="nlo">No Lunch<br />Ordered</div>';
        }

        if (!empty($addltxt)) {
            $body .= '<div class="addltxt">' . $addltxt . '</div>';
        }

        if (!empty($extcare)) {
            $body .= '<div class="extcare">' . $extcare . '</div>';
        }
        if ($editable)
            $body .= '</button>';

        $classes = '';
        $data = '';
        if ($curDateYMD == $todayYMD)
            $classes = 'today';
        if ($curTimestamp > $todayTimestamp) {
            if ($editable) {
                $classes .= ' editable';
                //$data = 'data-provid="'.$provID.'" data-dateymd="'.$curDateYMD.'" data-orderid="'.$orderID.'"';
            }
        } else {
            $classes .= ' past';
        }
        if (!empty($classes))
            $classes = ' class="' . trim($classes) . '"';

        return '<td ' . $classes . '>' . $body . '</td>';
    }

    public function getMenu(Request $request)
    {
        $thedate = Carbon::createFromTimestamp($request->ts);
        $user = User::find($request->uid);

        //UPDATE los_orderdetails AS od
        //INNER JOIN los_orders AS o ON od.order_id = o.id
        //SET od.lunchdate_id = o.lunchdate_id,od.user_id=o.user_id

        $lunchdate = LunchDate::select('los_lunchdates.id AS lunchdate_id', 'lp.id AS prov_id', 'provider_name', 'provider_image', 'provider_includes',
            'provide_date', 'allow_orders', 'orders_placed', 'additional_text', 'extended_care_text', 'provider_url')
            ->join('los_providers as lp', 'lp.id', '=', 'los_lunchdates.provider_id')
            ->where('provide_date', '=', $thedate)
            ->first();

        //why left join doesn't work?
        $menuitems = LunchDateMenuItem::select('menuitem_id', 'item_name', 'price', 'active')
            ->join('los_menuitems as mi', 'mi.id', '=', 'los_lunchdate_menuitems.menuitem_id')
            //->leftJoin('los_orderdetails as od', 'mi.id', '=', 'od.menuitem_id')
            ->where('lunchdate_id', $lunchdate->lunchdate_id)
            //->where('od.user_id',$request->uid)
            //->where('od.lunchdate_id','=',$lunchdate->lunchdate_id)
            ->orderBy('item_name')
            ->get();

        $orderdetails = OrderDetail::select('menuitem_id', 'qty', 'total_price')
            ->join('los_orders as o', 'o.id', '=', 'los_orderdetails.order_id')
            ->where('lunchdate_id', '=', $lunchdate->lunchdate_id)
            ->where('user_id', '=', $request->uid)
            ->get();

        $total_price = 0;
        if (count($orderdetails) > 0) {
            $total_price = $orderdetails[0]->total_price;
        }
        $v = view('orders.lunchmenu', ['thedate' => $thedate, 'lunchdate' => $lunchdate, 'user' => $user, 'menuitems' => $menuitems, 'orderdetails' => $orderdetails, 'totalprice' => $total_price])->render();
        return response()->json(array('error' => false, 'html' => $v));
    }

    /**
     * Create a new order.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //validate these?
        $account_id = $request->acctid;
        $user_id = $request->uid;
        $lunchdate_id = $request->lunchdateid;
        $thedate = Carbon::createFromTimestamp($request->ts);

        $lunchdate = LunchDate::select('orders_placed')
            ->where('provide_date', '=', $thedate)
            ->first();

        if ($lunchdate->orders_placed) {
            return response()->json(array('error' => false, 'amtdue' => $this->accounts->amountDue($account_id, false),
                'html' => $this->getOrderCell($account_id, $user_id, $thedate)));
        }

        //ref integrity will delete details
        $deletedRows = Order::where('user_id', $request->uid)->where('order_date', $thedate)->delete();

        $order_ids = array();
        $order_qtys = array();
        $inputs = $request->all();
        foreach ($inputs as $key => $value) {
            if (substr($key, 0, 4) == 'item') {
                $order_ids[] = intval(substr($key, 4));
                if ($value < 1 || $value > 2)
                    $order_qtys[] = 1;
                else
                    $order_qtys[] = $value;
            }
        }

        if (count($order_ids) == 0) {
            return response()->json(array('error' => false, 'amtdue' => $this->accounts->amountDue($account_id, true),
                'html' => $this->getOrderCell($account_id, $user_id, $thedate)));
        }

        $menuitems = MenuItem::select('id', 'item_name', 'price')
            ->whereIn('id', $order_ids)
            ->where('active', '=', 1)
            ->get();

        if (count($menuitems) == 0) {
            return response()->json(array('error' => false, 'amtdue' => $this->accounts->amountDue($account_id, true),
                'html' => $this->getOrderCell($account_id, $user_id, $thedate)));
        }

        $total_price = 0;
        $desc = '';
        foreach ($menuitems as $menuitem) {
            foreach ($order_ids as $key => $order_id) {
                if ($order_id == $menuitem->id) {
                    $total_price += $menuitem->price * $order_qtys[$key];
                    if ($order_qtys[$key] > 1)
                        $desc .= '(' . $order_qtys[$key] . 'x) ';
                    $desc .= $menuitem->item_name . ', ';
                    break;
                }
            }
        }

        $order = new Order();
        $order->account_id = $account_id;
        $order->user_id = $user_id;
        $order->lunchdate_id = $lunchdate_id;
        $order->order_date = $thedate;
        $order->short_desc = substr($desc, 0, -2);
        $order->total_price = $total_price;
        $order->status_code = config('app.status_code_unlocked');
        $order->entered_by_account_id = Auth::id();
        $order->save();

        foreach ($menuitems as $menuitem) {
            foreach ($order_ids as $key => $order_id) {
                if ($order_id == $menuitem->id) {
                    $od = new OrderDetail();
                    $od->order_id = $order->id;
                    $od->account_id = $account_id;
                    $od->menuitem_id = $menuitem->id;
                    $od->price = $menuitem->price;
                    $od->qty = $order_qtys[$key];
                    $od->save();
                    break;
                }
            }
        }

        return response()->json(array('error' => false, 'amtdue' => $this->accounts->amountDue($account_id, true),
            'html' => $this->getOrderCell($account_id, $user_id, $thedate)));
    }

    private function getOrderCell($account_id, $user_id, $thedate)
    {

        $today = Carbon::now()->setTime(0, 0, 0);
        $todayYMD = $today->toDateString();
        $todayTimestamp = strtotime($todayYMD);
        $thedateYMD = $thedate->toDateString();

        $lunchdate = LunchDate::select('los_lunchdates.id AS lunchdate_id', 'lp.id AS prov_id', 'provider_name', 'provider_image',
            'provide_date', 'allow_orders', 'orders_placed', 'additional_text', 'extended_care_text', 'provider_url')
            ->join('los_providers as lp', 'lp.id', '=', 'los_lunchdates.provider_id')
            ->where('provide_date', '=', $thedate)
            ->get();

        $nle = NoLunchException::select('id', 'exception_date', 'reason', 'description', 'grade_id', 'teacher_id')
            ->where('exception_date', '=', $thedate)
            ->get();

        $order = Order::select('id AS order_id', 'user_id', 'short_desc', 'order_date', 'total_price', 'status_code')
            ->where('order_date', '=', $thedate)
            ->get();

        $user = User::select('id', 'first_name', 'last_name', 'allowed_to_order', 'teacher_id', 'grade_id', 'user_type')
            ->where('id', '=', $user_id)
            ->first();

        $account = Account::select('enabled', 'privilege_level', 'no_new_orders')
            ->where('id', '=', $account_id)
            ->first();

        return $this->getOrderCellHTML($lunchdate, $nle, $user, $order, $todayYMD, $todayTimestamp, $thedateYMD, $account);
    }
}
