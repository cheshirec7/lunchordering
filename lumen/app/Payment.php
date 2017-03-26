<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    protected $table = 'los_payments';
    protected $fillable = ['account_id', 'pay_method', 'credit_amt', 'credit_date', 'credit_desc', 'deleted'];
}
