<?php

namespace App\Http\Controllers;

use App\LunchDate;
use App\NoLunchException;
use App\Order;
use Auth;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display an account's orders
     */
    public function doReport(Request $request)
    {
        $report = '';
        $thedate = new \DateTime();
        $thedate = $thedate->format('Y-m-d');

        $lunchdates = LunchDate::select(DB::raw("DATE_FORMAT(provide_date,'%W, %M %D, %Y') AS lunch_date"), 'provider_name', 'provide_date', 'additional_text', 'extended_care_text')
            ->join('los_providers as lp', 'lp.id', '=', 'los_lunchdates.provider_id')
            ->where('provide_date', ">=", $thedate)
            ->orderBy('provide_date')
            ->get();

        $nles = NoLunchException::select('exception_date', 'first_name', 'last_name', 'reason', 'description')
            ->join('los_users as u', 'u.grade_id', '=', 'los_nolunchexceptions.grade_id')
            ->join('los_accounts as a', 'a.id', '=', 'u.account_id')
            ->where('exception_date', ">=", $thedate)
            ->where('a.id', Auth::id())
            ->orderBy('exception_date')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $orders = Order::select('order_date', 'first_name', 'last_name', 'short_desc', 'status_code')
            ->join('los_users as u', 'u.id', '=', 'los_orders.user_id')
            ->where('los_orders.account_id', Auth::id())
            ->where('order_date', ">=", $thedate)
            ->orderBy('order_date')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        foreach ($lunchdates as $lunchdate) {

            $date_group = '';
            $numprinted = 0;

            $date_group .= '<div><b>' . $lunchdate->lunch_date . '</b> (' . $lunchdate->provider_name . ')</div>';
            $date_group .= '<div>';
            if ($lunchdate->additional_text) {
                if ($request->rpttype > 0)
                    $numprinted++;
                $date_group .= $lunchdate->additional_text;
            }

            if ($lunchdate->extended_care_text) {
                if ($request->rpttype > 0)
                    $numprinted++;
                $date_group .= ' * ' . $lunchdate->extended_care_text . ' * ';
            }

            $date_group .= '</div>';

            foreach ($nles as $nle) {
                if ($nle->exception_date == $lunchdate->provide_date) {
                     if ($request->rpttype > 0)
                        $numprinted++;
                    $date_group .= '<div>' . $nle->first_name . ' ' . $nle->last_name . ' - ' . $nle->reason . ' - ' . $nle->description . '</div>';
                }
            }

            foreach ($orders as $order) {
                if ($order->order_date == $lunchdate->provide_date) {
                    $numprinted++;
                    $date_group .= '<div>' . $order->first_name . ' ' . $order->last_name . ' - ' . $order->short_desc;
                    if ($order->status_code == 0)
                        $date_group .= ' <i>[scheduled]</i>';
                    $date_group .= '</div>';
                }
            }

            if ($numprinted == 0)
                $date_group .= '<div>No Lunches Ordered</div>';

            $date_group .= '<div>&nbsp;</div>';

            if ($request->rpttype == 2 || $numprinted > 0) {
                $report .= $date_group;
            }
        }
        return view('reports.accountorders', ['title' => 'Upcoming Lunch Orders', 'report' => $report]);
    }
}
