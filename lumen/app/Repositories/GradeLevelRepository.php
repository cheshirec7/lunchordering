<?php

namespace App\Repositories;

use App\GradeLevel;

class GradeLevelRepository
{
    /**
     * Get all of the valid gradelevels
     *
     * @return Collection
     */
    public function datatableAllEditable()
    {
        return GradeLevel::select('id as DT_RowId', 'grade', 'grade_desc', 'report_order')
            ->where('id', '>', config('app.gradelevel_na_id'))
            ->orderBy('report_order', 'asc');
    }

    public function assignedGradeLevels()
    {
        $w = 'los_gradelevels.id IN (SELECT distinct(grade_id) FROM los_users WHERE user_type=3 AND grade_id > 1)';//user_type_student
        return GradeLevel::select(array('id', 'grade_desc'))
            ->whereRaw($w)
            ->orderBy('report_order')
            ->get();
    }
}
