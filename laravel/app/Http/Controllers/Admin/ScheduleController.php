<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\LunchDate;
use App\LunchDateMenuItem;
use App\MenuItem;
use App\NoLunchException;
use App\Order;
use App\OrderDetail;
use App\Provider;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Display the schedule.
     *
     * @param  Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $providers = Provider::select('id', 'provider_name')
            ->where('allow_orders', '=', 1)
            ->orderBy('provider_name')
            ->get();
        return view('admin.schedule.index', ['thetable' => $this->getScheduleMonthHTML(0), 'providers' => $providers]);
    }

    private function getScheduleMonthHTML($periodNo)
    {
        $today = Carbon::now()->setTime(0, 0, 0);
        $start_month = $today->copy()->startOfMonth()->addMonths($periodNo);
        $end_month = $start_month->copy()->endOfMonth();
        if ($start_month->dayOfWeek == 6)
            $start_date = $start_month->copy()->addDays(1);
        else
            $start_date = $start_month->copy()->addDays(-$start_month->dayOfWeek);
        $end_date = $end_month->copy()->addDays(6 - $end_month->dayOfWeek);

        $lunchdates = LunchDate::select('los_lunchdates.id AS lunchdate_id', 'lp.id AS prov_id', 'provider_name', 'provider_image',
            'provide_date', 'allow_orders', 'orders_placed', 'additional_text', 'extended_care_text', 'provider_url')
            ->join('los_providers as lp', 'lp.id', '=', 'los_lunchdates.provider_id')
            ->whereBetween('provide_date', array($start_date, $end_date))
            ->orderBy('provide_date')
            ->get();

        $nleGrades = NoLunchException::select('exception_date', 'reason', 'description', 'grade_desc')
            ->join('los_gradelevels as gl', 'gl.id', '=', 'los_nolunchexceptions.grade_id')
            ->whereBetween('exception_date', array($start_date, $end_date))
            ->where('grade_id', '>', config('app.gradelevel_na_id'))
            ->orderBy('report_order')
            ->get();

        $nleTeachers = NoLunchException::select('exception_date', 'reason', 'description', 'first_name', 'last_name')
            ->join('los_users as u', 'u.id', '=', 'los_nolunchexceptions.teacher_id')
            ->whereBetween('exception_date', array($start_date, $end_date))
            ->where('los_nolunchexceptions.teacher_id', '>', config('app.teacher_na_id'))
            ->orderBy('last_name')
            ->get();

        $ordercounts = Order::select(DB::raw('COUNT(id) as order_count'), 'order_date')
            ->whereBetween('order_date', array($start_date, $end_date))
            ->groupBy('order_date')
            ->get();

        $res = '<table id="scheduletable" class="table table-bordered">';
        $res .= '<tr><th>Sun</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Sat</th></tr>';
        $res .= '<tr>';
        $finished = false;
        $dayno = 0;
        $cur_date = $start_date->copy();
        while (!$finished) {
            $ld = null;
            foreach ($lunchdates as $lunchdate) {
                if ($lunchdate->provide_date == $cur_date->toDateString()) {
                    $ld = $lunchdate;
                    break;
                }
            }
            $ordercount = 0;
            foreach ($ordercounts as $oc) {
                if ($oc->order_date == $cur_date->toDateString()) {
                    $ordercount = $oc->order_count;
                    break;
                }
            }

            $res .= '<td ';
            if ($cur_date->getTimestamp() > $today->getTimestamp() && $cur_date->isWeekday()) {
                $res .= ' class="enabled" data-ts="' . $cur_date->getTimestamp() . '"';
            }
            $res .= '>';

            $res .= $this->buildCellContents($cur_date, $ld, $ordercount, $nleGrades, $nleTeachers);

            $res .= '</td>';
            $dayno += 1;
            if ($dayno == 7) {
                $res .= '</tr>';
                $dayno = 0;
            }
            $cur_date->addDay();
            $finished = ($cur_date->getTimestamp() > $end_date->getTimestamp());
        }
        $res .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr></table>';
        return $res;
    }

    private function buildCellContents(Carbon $cur_date, $oLunchDate, $ordercount, $nleGrades, $nleTeachers)
    {

        if ($cur_date->isWeekend())
            return '';
        $res = '<span>' . $cur_date->format('j') . '</span>';

        if ($oLunchDate) {
            //$enabled = (($cur_date->getTimestamp() > $today->getTimestamp()) && (is_null($oLunchDate->orders_placed)));

            $res .= '<img src="/img/providers/' . $oLunchDate->provider_image . '" alt="' . $oLunchDate->provider_name . '" title="' . $oLunchDate->provider_name . '">';

            if ($oLunchDate->additional_text) {
                $res .= '<div class="addltxt">' . $oLunchDate->additional_text . '</div>';
            }
            if ($oLunchDate->extended_care_text) {
                $res .= '<div class="extcare">' . $oLunchDate->extended_care_text . '</div>';
            }
        }

        $exceptions = '';
        foreach ($nleGrades as $nle) {
            if ($nle->exception_date == $cur_date->toDateString()) {
                $exceptions .= $nle->grade_desc . ': ' . $nle->reason . '<br />';
            }
        }

        foreach ($nleTeachers as $nle) {
            if ($nle->exception_date == $cur_date->toDateString()) {
                $exceptions .= $nle->first_name . ' ' . $nle->last_name . ': ' . $nle->reason . '<br />';
            }
        }

        if ($exceptions) {
            $res .= '<div class="nle">' . substr($exceptions, 0, -6) . '</div>';
        }

        if (($oLunchDate) && ($ordercount > 0)) {
            if ($oLunchDate->orders_placed)
                $res .= '<div class="oc">' . $ordercount . ' Lunches Ordered</div>';
            else
                $res .= '<div class="oc">' . $ordercount . ' Orders Scheduled</div>';
        }

        return $res;
    }

    public function show($id)
    {
        return response()->json(array('error' => false, 'html' => $this->getScheduleMonthHTML($id)));
    }

    /**
     * Create a new schedule item.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $cur_date = Carbon::createFromTimestamp($request->ts);
        $inputs = $request->all();

        $provider_id = 0;
        if (array_key_exists('provider_id', $inputs))
            $provider_id = $inputs['provider_id'];
        $orderdetails = array();
        $lunchdate = LunchDate::where('provide_date', $cur_date)->first();

        if ($lunchdate) {
            if ($lunchdate->orders_placed)
                return response()->json(array('error' => false, 'html' => $this->getScheduleDayHTML($cur_date)));

            if ($provider_id == 0) {
                $deletedRows = LunchDateMenuItem::where('lunchdate_id', $lunchdate->id)->delete();
                $lunchdate->delete();
                return response()->json(array('error' => false, 'html' => $this->getScheduleDayHTML($cur_date)));
            }

            $orderdetails = OrderDetail::select(DB::raw('COUNT(qty) as thecount'), 'menuitem_id')
                ->join('los_orders as o', 'o.id', '=', 'los_orderdetails.order_id')
                ->where('lunchdate_id', $lunchdate->id)
                ->groupBy('menuitem_id')
                ->get();

            $deletedRows = LunchDateMenuItem::where('lunchdate_id', $lunchdate->id)->delete();

            if (count($orderdetails) == 0)
                $lunchdate->provider_id = $provider_id;
        } else {
            if ($provider_id == 0)
                return response()->json(array('error' => false, 'html' => $this->getScheduleDayHTML($cur_date)));

            $lunchdate = new LunchDate();
            $lunchdate->provide_date = $cur_date;
            $lunchdate->provider_id = $provider_id;
        }

        $lunchdate->additional_text = filter_var($inputs['addmsg'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $lunchdate->extended_care_text = filter_var($inputs['ecmsg'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $lunchdate->save();

        $item_ids = array();
        foreach ($inputs as $key => $value) {
            if (substr($key, 0, 4) == 'item') {
                $item_ids[] = intval(substr($key, 4));
            }
        }

        foreach ($orderdetails as $od) {
            $item_ids[] = $od->menuitem_id;
        }

        //TODO: what if order scheduled then menuitem deactivated?
        $menuitems = MenuItem::select('id')
            ->whereIn('id', $item_ids)
            ->where('provider_id', $lunchdate->provider_id)
            ->where('active', 1)
            ->get();

        foreach ($menuitems as $menuitem) {
            $ldmi = new LunchDateMenuItem();
            $ldmi->lunchdate_id = $lunchdate->id;
            $ldmi->menuitem_id = $menuitem->id;
            $ldmi->save();
        }

        return response()->json(array('error' => false, 'html' => $this->getScheduleDayHTML($cur_date)));
    }

    /////

    private function getScheduleDayHTML(Carbon $cur_date)
    {
        $lunchdate = LunchDate::select('los_lunchdates.id AS lunchdate_id', 'p.id AS prov_id', 'provider_name', 'provider_image',
            'provide_date', 'allow_orders', 'orders_placed', 'additional_text', 'extended_care_text', 'provider_url')
            ->join('los_providers as p', 'p.id', '=', 'los_lunchdates.provider_id')
            ->where('provide_date', $cur_date)
            ->first();

        $nleGrades = NoLunchException::select('exception_date', 'reason', 'description', 'grade_desc')
            ->join('los_gradelevels as gl', 'gl.id', '=', 'los_nolunchexceptions.grade_id')
            ->where('exception_date', $cur_date)
            ->where('grade_id', '>', config('app.gradelevel_na_id'))
            ->orderBy('report_order')
            ->get();

        $nleTeachers = NoLunchException::select('exception_date', 'reason', 'description', 'first_name', 'last_name')
            ->join('los_users as u', 'u.id', '=', 'los_nolunchexceptions.teacher_id')
            ->where('exception_date', $cur_date)
            ->where('los_nolunchexceptions.teacher_id', '>', config('app.teacher_na_id'))
            ->orderBy('last_name')
            ->get();

        $ordercount = 0;
        if ($lunchdate) {
            $oc = Order::select(DB::raw('COUNT(id) as order_count'))
                ->where('lunchdate_id', $lunchdate->lunchdate_id)
                ->first();
            if ($oc)
                $ordercount = $oc->order_count;
        }

        return $this->buildCellContents($cur_date, $lunchdate, $ordercount, $nleGrades, $nleTeachers);
    }

    /////
    public function getProviderMenuItemsHTML(Request $request)
    {
        $inputs = $request->only('ts', 'pid', 'use_saved');
        $thedate = Carbon::createFromTimestamp(intval($inputs['ts']));
        $selected_provider_id = intval($inputs['pid']);
        $lunchdate_id = 0;
        $orders_placed = false;
        $has_orders = false;
        $default_price = config('app.menuitem_default_price');
        $orderdetails = array();
        $add_text = '';
        $ec_text = '';

        $lunchdate = LunchDate::select('id', 'provider_id', 'orders_placed', 'additional_text', 'extended_care_text')
            ->where('provide_date', $thedate)
            ->first();

        if ($lunchdate) {
            $orders_placed = $lunchdate->orders_placed;
            $lunchdate_id = $lunchdate->id;
            $add_text = $lunchdate->additional_text;
            $ec_text = $lunchdate->extended_care_text;

            $orderdetails = OrderDetail::select(DB::raw('COUNT(qty) as thecount'), 'menuitem_id')
                ->join('los_orders as o', 'o.id', '=', 'los_orderdetails.order_id')
                ->where('lunchdate_id', $lunchdate_id)
                ->groupBy('menuitem_id')
                ->get();
            $has_orders = count($orderdetails) > 0;
            if ($inputs['use_saved'] || $has_orders)
                $selected_provider_id = $lunchdate->provider_id;
        }

        if ($selected_provider_id <= config('app.provider_lunchprovided_id'))
            return response()->json(array('error' => false,
                'pid' => $selected_provider_id,
                'html' => '',
                'orders_placed' => $orders_placed,
                'has_orders' => $has_orders,
                'add_text' => $add_text,
                'ec_text' => $ec_text));

        $menuitems = MenuItem::select('los_menuitems.id', 'item_name', 'price')
            ->join('los_providers as p', 'p.id', '=', 'los_menuitems.provider_id')
            ->where('p.id', $selected_provider_id)
            ->where('active', 1)
            ->orderBy('item_name')
            ->get();

        $lunchdatemenuitems = LunchDateMenuItem::select('menuitem_id')
            ->where('lunchdate_id', $lunchdate_id)
            ->get();

        $res = '';
        foreach ($menuitems as $menuitem) {
            $name = $menuitem->item_name;
            if ($menuitem->price != $default_price) {
                $name .= ' ($' . number_format($menuitem->price / 100, 2) . ')';
            }
            $checked = '';
            $ordered = false;
            $previously_selected = false;

            foreach ($orderdetails as $orderdetail) {
                if ($orderdetail->menuitem_id == $menuitem->id) {
                    $ordered = true;
                    break;
                }
            }

            if (!$ordered) {
                foreach ($lunchdatemenuitems as $lunchdatemenuitem) {
                    if ($lunchdatemenuitem->menuitem_id == $menuitem->id) {
                        $previously_selected = true;
                        break;
                    }
                }
            }

            if ($ordered || $previously_selected || $inputs['use_saved'] == 0) {
                $checked = ' checked';
            }


            $disabled = '';
            if ($orders_placed || $ordered)
                $disabled = ' disabled';

            $res .= '<div><input type="checkbox" class="filled-in" id="item' . $menuitem->id . '" name="item' . $menuitem->id . '"' . $checked . $disabled . '>';
            $res .= '<label for="item' . $menuitem->id . '">' . $name . '</label></div>';
        }

        return response()->json(array('error' => false,
            'pid' => $selected_provider_id,
            'html' => $res,
            'orders_placed' => $orders_placed,
            'has_orders' => $has_orders,
            'add_text' => $add_text,
            'ec_text' => $ec_text));
    }
}
