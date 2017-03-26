<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $table = 'los_gradelevels';
    protected $fillable = ['grade', 'grade_desc', 'report_order'];
}
