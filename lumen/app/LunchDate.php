<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LunchDate extends Model
{
    protected $table = 'los_lunchdates';
    protected $fillable = ['provider_id', 'provide_date', 'orders_placed', 'additional_text', 'extended_care_text'];
}
