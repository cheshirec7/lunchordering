<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'los_orderdetails';
    protected $fillable = ['account_id', 'order_id', 'menuitem_id', 'lunchdate_id', 'user_id', 'qty', 'price'];

    /**
     * Get the account that owns the order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
