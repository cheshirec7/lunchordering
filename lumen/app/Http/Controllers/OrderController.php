<?php

namespace App\Http\Controllers;

//use Log;
use App\Account;
use App\LunchDate;
use App\LunchDateMenuItem;
use App\MenuItem;
use App\NoLunchException;
use App\Order;
use App\OrderDetail;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$results = app('db')->select("SELECT id,account_name FROM los_accounts");
        return response()->json(['data' => User::select('id', 'account_name', 'email', 'enabled')->take(3)->get()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $req = (object)$request->json()->all();

        $orderdate = Carbon::createFromTimestamp($req->ts);

        $lunchdate = LunchDate::select('id', 'orders_placed')
            ->where('provide_date', $orderdate)
            ->first();

        if ($lunchdate->orders_placed)
            return response()->json(array('data' => 'ok'));

        //ref integrity will delete details
        $deletedRows = Order::where('user_id', $req->uid)->where('order_date', $orderdate)->delete();

        if (count($req->menuids) == 0)
            return response()->json(array('data' => 'ok'));

        $menuitems = MenuItem::select('id', 'item_name', 'price')
            ->whereIn('id', $req->menuids)
            ->where('active', 1)
            ->get();

        if (count($menuitems) == 0)
            return response()->json(array('data' => 'ok'));

        $total_price = 0;
        $desc = '';
        foreach ($req->menuids as $idx => $menu_id) {
            foreach ($menuitems as $menuitem) {
                if ($menu_id == $menuitem->id) {
                    $total_price += $menuitem->price * $req->qtys[$idx];
                    if ($req->qtys[$idx] > 1)
                        $desc .= '(' . $req->qtys[$idx] . 'x) ';
                    $desc .= $menuitem->item_name . ', ';
                    break;
                }
            }
        }

        $order = new Order();
        $order->account_id = $req->acctid;
        $order->user_id = $req->uid;
        $order->lunchdate_id = $lunchdate->id;
        $order->order_date = $orderdate;
        $order->short_desc = substr($desc, 0, -2);
        $order->total_price = $total_price;
        $order->status_code = 0;//config('app.status_code_unlocked');
        $order->entered_by_account_id = $req->acctid;
        $order->save();
        foreach ($req->menuids as $idx => $menu_id) {
            foreach ($menuitems as $menuitem) {
                if ($menu_id == $menuitem->id) {
                    $od = new OrderDetail();
                    $od->order_id = $order->id;
                    $od->account_id = $req->acctid;
                    $od->menuitem_id = $menu_id;
                    $od->price = $menuitem->price;
                    $od->qty = $req->qtys[$idx];
                    $od->save();
                    break;
                }
            }
        }
        return response()->json(array('data' => 'ok'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */

    public function show($id, $request)
    {

    }

    public function getAccountForPeriod($accid, $period)
    {
//                return response()->json(['error' => 'invalid_credentials'], 401);
        $account_id = intval($accid); //check if not admin and wrong acct
        $today = Carbon::now()->setTime(0, 0, 0);
        $todayYMD = $today->toDateString();
        $todayTimestamp = $today->getTimestamp();//strtotime($todayYMD);
        if ($today->dayOfWeek === Carbon::SATURDAY) {
            $start = $today->addDays(2);
        } else if ($today->dayOfWeek === Carbon::SUNDAY) {
            $start = $today->addDays(1);
        } else
            $start = $today->startOfWeek();
        $start->addDays($period * 7); //monday
        $end = $start->copy()->addDays(4); //friday

        $users = User::select('id', 'first_name', 'allowed_to_order', 'teacher_id', 'grade_id', 'user_type')
            ->where('account_id', '=', $account_id)
            ->orderBy('first_name')
            ->get();
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
        $account = Account::select('enabled', 'privilege_level', 'no_new_orders')
            ->where('id', '=', $account_id)
            ->first();

        $loopDate = $start->copy();
        $arr = array();

        foreach ($users as $user) {
            $head = new \stdClass();
            $head->name = $user->first_name;

            $loopDate = $start->copy();
            //loop mon-fri
            for ($i = 0; $i < 5; $i++) {
                $loopDateYMD = $loopDate->toDateString();

                $details = new \stdClass();
                $head->details[] = $details;

                $details->timestamp = strtotime($loopDateYMD);
                $details->userid = $user->id;
                $details->date = $loopDate->format("l F jS");
                $details->today = $loopDateYMD == $todayYMD;
                $details->shortdate = $loopDate->format("M jS");

                $details->allowOrders = false;
                $details->ordersPlaced = false;
                $details->editable = false;
                $details->nlo = false;
                $details->addltxt = '';
                $details->extcare = '';
                $details->nleReason = '';
                $details->nleDesc = '';
                $details->lunchText = '';
                $details->img = '';

                foreach ($lunchdates as $lunchdate) {
                    if (date("Y-m-d", strtotime($lunchdate->provide_date)) == $loopDateYMD) {
                        $details->img = $lunchdate->provider_image;
                        $details->addltxt = $lunchdate->additional_text;
                        $details->extcare = $lunchdate->extended_care_text;
                        $details->allowOrders = $lunchdate->allow_orders;
                        $details->ordersPlaced = !empty($lunchdate->orders_placed);
                        break;
                    }
                }

                foreach ($nles as $nle) { //exceptions
                    if (date("Y-m-d", strtotime($nle->exception_date)) == $loopDateYMD) {
                        if ($user->grade_id == $nle->grade_id) {
                            $details->nleReason = $nle->reason;
                            $details->nleDesc = $nle->description;
                            break;
                        }
                    }
                }

                if (empty($details->nleReason)) {
                    foreach ($orders as $order) {
                        if ((date("Y-m-d", strtotime($order->order_date)) == $loopDateYMD) && ($user->id == $order->user_id)) {
                            $details->lunchText = $order->short_desc;
                            break;
                        }
                    }
                }

                if (empty($details->nleReason)) {
                    if (!empty($details->lunchText)) {
                        $details->editable = $details->timestamp > $todayTimestamp && !$details->ordersPlaced;
                    } else if ($details->timestamp > $todayTimestamp && !$details->ordersPlaced && $details->allowOrders) {
                        $details->editable = $user->allowed_to_order && $account->no_new_orders == 0 && $account->enabled;
                    } else if ($details->allowOrders)
                        $details->nlo = true;
                }

                $loopDate->addDay();
            }
            $arr[] = $head;
        }

        return response()->json(['data' => $arr]);
    }

    public function getMenu($ts, $uid)
    {
//        return response()->json(['error' => 'invalid_credentials'], 401);
        $thedate = Carbon::createFromTimestamp($ts);
        $user = User::find($uid);

        $lunchdate = LunchDate::select('los_lunchdates.id AS lunchdate_id', 'provider_name', 'provider_image', 'provider_includes', 'provider_url')
            ->join('los_providers as lp', 'lp.id', '=', 'los_lunchdates.provider_id')
            ->where('provide_date', $thedate)
            ->first();

        $menuitems = LunchDateMenuItem::select('menuitem_id', 'item_name', 'price')
            ->join('los_menuitems as mi', 'mi.id', '=', 'los_lunchdate_menuitems.menuitem_id')
            ->where('lunchdate_id', $lunchdate->lunchdate_id)
            ->orderBy('item_name')
            ->get();

        $orderdetails = OrderDetail::select('menuitem_id', 'qty')
            ->join('los_orders as o', 'o.id', '=', 'los_orderdetails.order_id')
            ->where('lunchdate_id', $lunchdate->lunchdate_id)
            ->where('user_id', $uid)
            ->get();

        $data = new \stdClass();
        $data->date = $thedate->format("l, F jS");
        $data->purl = $lunchdate->provider_url;
        $data->pimage = $lunchdate->provider_image;
        $data->pname = $lunchdate->provider_name;
        $data->pincludes = $lunchdate->provider_includes;
        $data->fname = $user->first_name;
        $data->lname = $user->last_name;

        foreach ($menuitems as $menuitem) {
            $details = new \stdClass();
            $data->menuitems[] = $details;
            $details->id = $menuitem->menuitem_id;
            $details->name = $menuitem->item_name;
            if ($menuitem->price != '500')
                $details->name .= ' ($' . number_format($menuitem->price / 100, 2) . ')';
            $details->price = $menuitem->price;
            $details->qty = 0;
            $details->checked = false;

            foreach ($orderdetails as $orderdetail) {
                if ($orderdetail->menuitem_id == $menuitem->menuitem_id) {
                    $details->qty = $orderdetail->qty;
                    $details->checked = true;
                    break;
                }
            }
        }

        return response()->json(['data' => $data]);
//        return $this->api(json_encode($data));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //$user = User::find($id);
        //$user->name = 'New Name';
        //$user->save();
        return response()->json(['success' => 'user_updated'], 204); //or 200
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $user = User::find($id);
        // $user->delete();
        // or
        // User::destroy($id);
        return response()->json(['success' => 'user_deleted'], 204); //or 200
    }


    ////////////////////////////////////////////////////////////////////////////
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
}
