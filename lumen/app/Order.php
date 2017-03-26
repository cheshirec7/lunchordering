<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'los_orders';
    protected $fillable = ['short_desc'];

    /**
     * Get the account that owns the order.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
