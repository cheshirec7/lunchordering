<?php

namespace App\Repositories;

use App\MenuItem;
use DB;

class MenuItemRepository
{
    /**
     * Get all of the valid menuitems
     *
     * @return Collection
     */
    public function datatableForProviderAll($provider_id)
    {
        return MenuItem::select(DB::raw('los_menuitems.id AS DT_RowId, item_name, los_menuitems.price, active, count(qty) as thecount'))
            ->leftJoin('los_orderdetails', 'los_menuitems.id', '=', 'los_orderdetails.menuitem_id')
            ->where('los_menuitems.provider_id', '=', $provider_id)
            ->groupBy('los_menuitems.id', 'item_name', 'price', 'active')
            ->orderBy('item_name', 'asc');
    }

    public function datatableForProviderActiveOnly($provider_id)
    {
        return MenuItem::select('id as DT_RowId', 'item_name', 'price', 'active')
            ->where('provider_id', '=', $provider_id)
            ->where('active', '=', 1)
            ->orderBy('item_name', 'asc');
    }
}
