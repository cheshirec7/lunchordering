<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LunchDateMenuItem extends Model
{
    protected $table = 'los_lunchdate_menuitems';
    protected $fillable = ['lunchdate_id', 'menuitem_id'];
}