<?php

namespace App\Repositories;

use App\User;
use DB;

class UserRepository
{
    /**
     * Get all of the teachers
     *
     * @param  Account $account
     * @return Collection
     */

    //SELECT los_users.id as id,last_name,first_name,grade_desc
    //FROM los_users
    //LEFT JOIN los_gradelevels gl ON gl.id=los_users.grade_id
    //WHERE user_type=3
    public function allTeachers()
    {
        return User::leftjoin('los_gradelevels as gl', 'gl.id', '=', 'los_users.grade_id')
            ->select(array(
                'last_name',
                'first_name',
                'gl.grade_desc',
                'los_users.id as id'))
            ->where('user_type', '=', config('app.user_type_teacher'))
            ->orderBy('last_name')
            ->get();
    }

    public function assignedTeachers()
    {
        $s = 'los_users.id, CONCAT(last_name, ", ", first_name, " (", grade_desc, ")") as teacher_name';
        $w = 'los_users.id in (SELECT distinct(teacher_id) FROM los_users WHERE user_type=3 and teacher_id > 1)';
        return User::selectRaw($s)
            ->whereRaw($w)
            ->join('los_gradelevels', 'los_users.grade_id', '=', 'los_gradelevels.id')
            ->orderBy('teacher_name')
            ->get();
    }

    //SELECT los_users.id as id,CONCAT(los_users.lastName,", ",los_users.firstName) AS username,CONCAT(u1.lastName,", ",u1.firstName) AS teacherName,gl.gradeDesc,los_users.type,los_users.allowedToOrder,count(o.id)
    //FROM los_users
    //INNER JOIN los_users u1 ON u1.id=los_users.teacher_id
    //INNER JOIN los_gradelevels gl ON gl.id=los_users.grade_id
    //INNER JOIN los_orders o on o.user_id=los_users.id
    //WHERE los_users.account_id=216
    //AND status==??
    //group by los_users.id
    public function datatableForAccount($account_id)
    {
        $users = User::join('los_accounts AS a', 'a.id', '=', 'los_users.account_id')
            ->join('los_users AS teachers', 'teachers.id', '=', 'los_users.teacher_id')
            ->join('los_gradelevels AS gl', 'gl.id', '=', 'los_users.grade_id')
            //->leftjoin('los_orders AS o', 'o.user_id', '=', 'los_users.id')
            ->where('los_users.account_id', '>', config('app.default_account_id'))
            ->select(
                'los_users.id AS DT_RowId',
                'los_users.last_name',
                'los_users.first_name',
                DB::raw('CONCAT(teachers.last_name,", ",teachers.first_name) AS teacher_name'),
                'gl.grade_desc',
                'los_users.user_type',
//                DB::raw('COUNT(o.id) AS num_orders'),
                'los_users.allowed_to_order',
                'los_users.user_type as user_type_int',
                'gl.id AS gradelevel_id',
                'teachers.id AS teacher_id',
                'los_users.allowed_to_order as allowed_to_order_int',
                'los_users.account_id as account_id',
                'a.account_name as account_name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->when($account_id > 0, function ($query) use ($account_id) {
                return $query->where('los_users.account_id', $account_id);
            });

        return $users;
    }

    public function usersForAccount($account_id)
    {
        return User::select('id', 'last_name', 'first_name')
            ->where('account_id', $account_id)
            ->get();
    }

    public function getTransferNames($lunchdate_id, $user_id)
    {
        $s = 'id, CONCAT(last_name, ", ", first_name) as name';
        $w = 'id not in (SELECT user_id FROM los_orders WHERE lunchdate_id=' . $lunchdate_id . ')';
        return User::selectRaw($s)
            ->whereRaw($w)
            ->where('id', '>', config('app.default_user_id'))
            ->where('id', '!=', $user_id)
            ->orderBy('name')
            ->get();
    }
}
