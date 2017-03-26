<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'los_menuitems';
    protected $fillable = ['provider_id', 'item_name', 'price', 'active'];
}
