<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NoLunchException extends Model
{
    protected $table = 'los_nolunchexceptions';
    protected $fillable = ['exception_date', 'grade_id', 'teacher_id', 'reason', 'description'];
}
