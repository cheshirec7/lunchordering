<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $table = 'los_providers';
    protected $fillable = ['provider_name', 'provider_image', 'provider_url'];
}
