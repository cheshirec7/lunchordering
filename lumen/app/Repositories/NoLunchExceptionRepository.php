<?php

namespace App\Repositories;

use App\NoLunchException;
use App\Order;
use DB;

class NoLunchExceptionRepository
{
    /**
     * Get all of the valid nolunchexceptions
     *
     * @return Collection
     */
    public function datatableTodayAndLater()
    {
        return NoLunchException::leftjoin('los_gradelevels as gl', 'gl.id', '=', 'los_nolunchexceptions.grade_id')
            ->leftjoin('los_users as u', 'u.id', '=', 'los_nolunchexceptions.teacher_id')
            ->select(array('los_nolunchexceptions.id as DT_RowId', DB::raw('DATE_FORMAT(exception_date,"%m/%d/%Y") AS exception_date'),
                'grade_desc as teacher_grade', DB::raw('CONCAT(last_name,", ",first_name) as teacher_name'), 'reason', 'description',
                'los_nolunchexceptions.teacher_id', 'los_nolunchexceptions.grade_id'))
            //->where('exception_date','>=',date('Y-m-d'))
            ->orderBy('exception_date', 'asc');
    }

    public function exceptionExists($date, $teacher_id, $grade_id)
    {
        return (NoLunchException::select(array('id'))
            ->where('exception_date', '=', $date)
            ->where('teacher_id', '=', $teacher_id)
            ->where('grade_id', '=', $grade_id)
            ->count()) > 0;
    }

    //        SELECT COUNT(u.id)
//FROM los_users u
//INNER JOIN los_orders o ON u.id=o.user_id
//WHERE teacher_id=21
//    AND order_date='2015-08-20'
//SELECT COUNT(id) FROM los_users WHERE teacher_id=21 AND id IN (SELECT user_id FROM los_orders WHERE order_date='2015-08-20')
    public function numOrdersDateTeacher(\DateTime $date, $teacher_id)
    {
        //$dt = "'".$date->format('Y-m-d')."'";
        $res = Order::join('los_users as u', 'u.id', '=', 'los_orders.user_id')
            ->where('los_orders.order_date', $date)
            ->where('teacher_id', $teacher_id)
            ->count();
        return $res;
    }

    public function numOrdersDateGrade(\DateTime $date, $grade_id)
    {
        //$dt = "'".$date->format('Y-m-d')."'";
        $res = Order::join('los_users as u', 'u.id', '=', 'los_orders.user_id')
            ->where('los_orders.order_date', $date)
            ->where('grade_id', $grade_id)
            ->count();
        return $res;
    }
}
