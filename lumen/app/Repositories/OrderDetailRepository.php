<?php

namespace App\Repositories;

use App\OrderDetail;

class OrderDetailRepository
{
    public function adminAccountDetailReport($account_id)
    {
        return OrderDetail::select('first_name', 'last_name', 'order_date', 'item_name', 'qty', 'status_code', 'los_orderdetails.price')
            ->join('los_orders as o', 'o.id', '=', 'los_orderdetails.order_id')
            ->join('los_users as u', 'u.id', '=', 'o.user_id')
            ->join('los_menuitems as mi', 'mi.id', '=', 'los_orderdetails.menuitem_id')
            ->where('los_orderdetails.account_id', $account_id)
            ->orderBy('o.order_date')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

}
