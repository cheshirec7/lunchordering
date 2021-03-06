<?php

namespace App\Repositories;

use App\Account;
use App\Order;
use App\User;
use DB;

class OrderRepository
{

    public function forUser(User $user)
    {
        return Order::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function forAccount(Account $account)
    {
        return Order::where('account_id', $account->id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get all of the orders for a given account.
     *
     * @param  $id (accountID)
     * @return Collection
     */
    public function datatableForLunchdateID($id)
    {
        //DB::raw('CONCAT(u2.last_name,", ",u2.first_name," / ",grade_desc) as teacher_name_grade'),
        /*
         select los_orders.id as DT_RowId,CONCAT(u1.last_name,", ",u1.first_name) as name, short_desc,total_price,grade_desc,
        u1.teacher_id as teacher_id,status_code,notes,status_code as status_code_int,los_orders.user_id
        from los_orders
            inner join los_users as u1 on u1.id = los_orders.user_id
            inner join los_gradelevels as gl on gl.id = u1.grade_id
            where lunchdate_id = 48
          */
        return Order::select('los_orders.id as DT_RowId',
            DB::raw('CONCAT(u1.last_name,", ",u1.first_name) as name'),
            'short_desc', 'total_price', 'grade_desc',
            'u1.teacher_id as teacher_id', 'status_code', 'notes', 'status_code as status_code_int', 'los_orders.user_id', 'u1.grade_id')
            ->join('los_users as u1', 'u1.id', '=', 'los_orders.user_id')
            //->join('los_users as u2', 'u2.id', '=', 'u1.grade_id')
            ->join('los_gradelevels as gl', 'gl.id', '=', 'u1.grade_id')
            ->where('lunchdate_id', '=', $id);
    }

    public function datatableForUserID($id)
    {
        return Order::select('los_orders.id as DT_RowId', 'order_date', 'short_desc', 'total_price')
            ->where('user_id', $id)
            ->orderBy('order_date');
    }

    public function myAccountAggregates($account_id)
    {
//        select count(los_orders.id) as order_count, COALESCE(sum(total_price),0) as total_price, first_name, last_name, user_id
//from los_orders
//inner join los_users as u on u.id = los_orders.user_id
//where los_orders.account_id = 4
//group by user_id,first_name,last_name
//order by first_name asc

        return Order::select(DB::raw('count(los_orders.id) as order_count'), DB::raw('COALESCE(sum(total_price),0) as total_price'), 'first_name', 'last_name', 'user_id')
            ->join('los_users as u', 'u.id', '=', 'los_orders.user_id')
            ->where('los_orders.account_id', $account_id)
            ->groupBy('user_id')
            ->groupBy('first_name')
            ->groupBy('last_name')
            ->get();
    }

    public function userReport($account_id)
    {
        return Order::select(DB::raw("DATE_FORMAT(order_date,'%W, %M %D, %Y') AS lunch_date"), 'first_name', 'last_name', 'short_desc', 'status_code')
            ->join('los_users as u', 'u.id', '=', 'los_orders.user_id')
            ->where('los_orders.account_id', $account_id)
            ->where('order_date', ">=", new \DateTime())
            ->orderBy('order_date', 'asc')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function adminProviderReport($order_date)
    {
        return Order::select(DB::raw('sum(qty) AS qty'), 'item_name')
            ->join('los_orderdetails as od', 'od.order_id', '=', 'los_orders.id')
            ->join('los_menuitems as mi', 'mi.id', '=', 'od.menuitem_id')
            ->where('los_orders.order_date', $order_date)
            ->groupBy('item_name')
            ->get();
    }

    public function adminOrdersByTeacherReport($order_date)
    {
        return Order::select('u1.first_name as o_fname', 'u1.last_name as o_lname', 'short_desc', 'u2.first_name as t_fname', 'u2.last_name as t_lname')
            ->join('los_users as u1', 'u1.id', '=', 'los_orders.user_id')
            ->join('los_users as u2', 'u2.id', '=', 'u1.teacher_id')
            ->where('order_date', $order_date)
            ->orderBy('u2.last_name')
            ->get();
    }

    public function adminOrdersByGradeReport($order_date)
    {
        return Order::select('first_name', 'last_name', 'short_desc', 'grade_desc')
            ->join('los_users as u', 'u.id', '=', 'los_orders.user_id')
            ->join('los_gradelevels as gl', 'gl.id', '=', 'u.grade_id')
            ->where('order_date', $order_date)
            ->orderBy('report_order')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function adminLunchLabels($order_date)
    {
        return Order::select('first_name', 'last_name', 'short_desc', 'grade_desc')
            ->join('los_users as u', 'u.id', '=', 'los_orders.user_id')
            ->join('los_gradelevels as gl', 'gl.id', '=', 'u.grade_id')
            ->where('order_date', $order_date)
            ->orderBy('report_order')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function adminAccountDetailReport($account_id)
    {
        return Order::select(DB::raw("DATE_FORMAT(order_date,'%W, %M %D, %Y') AS lunch_date"), 'first_name', 'last_name', 'short_desc', 'status_code')
            ->join('los_users as u', 'u.id', '=', 'los_orders.user_id')
            ->where('los_orders.account_id', $account_id)
            ->orderBy('order_date', 'asc')
            ->orderBy('last_name', 'first_name')
            ->get();
    }

}
