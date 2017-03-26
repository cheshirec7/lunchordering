<?php

namespace App\Repositories;

use App\LunchDate;
use DB;

class LunchDateRepository
{
    /**
     * Get lunch dates
     *
     * @return Collection
     */
    public function datesWithOrders()
    {
        //select provide_date,orders_placed,DATEDIFF(provide_date,curdate()) AS daysfromtoday from los_lunchdates where provide_date IN (SELECT DISTINCT order_date FROM los_orders) order by provide_date desc
        return LunchDate::select('id as lunchdate_id', 'provide_date', 'orders_placed', DB::raw('DATEDIFF(provide_date,curdate()) AS daysfromtoday'))
            ->whereRaw('provide_date IN (SELECT DISTINCT order_date FROM los_orders)')
            ->orderBy('provide_date', 'desc')
            ->get();

//        return LunchDate::select('provide_date','orders_placed', DB::raw('DATEDIFF(provide_date,curdate()) AS daysfromtoday'))
//          ->join('los_orders', 'order_date', '=', 'los_lunchdates.provide_date')
//          ->orderBy('provide_date', 'desc')
//          ->get();
    }

    public function updateOrdersPlaced($lunchdate_id)
    {
//        DB::statement('UPDATE los_lunchdates SET )');
    }

}
