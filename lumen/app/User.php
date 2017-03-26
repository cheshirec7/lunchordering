<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'los_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['account_id', 'first_name', 'last_name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    //protected $hidden = [];

    /**
     * Get all of the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
