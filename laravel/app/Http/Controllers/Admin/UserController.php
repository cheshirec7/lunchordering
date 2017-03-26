<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Repositories\AccountRepository;
use App\Repositories\GradeLevelRepository;
use App\Repositories\UserRepository;
use App\User;
use Datatables;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{

    protected $users;
    protected $accounts;
    protected $gradelevels;

    public function __construct(UserRepository $users,
                                AccountRepository $accounts,
                                GradeLevelRepository $gradelevels)
    {
        $this->users = $users;
        $this->accounts = $accounts;
        $this->gradelevels = $gradelevels;
    }

    public function index(Request $request)
    {
        return view('admin.users.index',
            [
                'accounts' => $this->accounts->allEditableAcounts()->get(),
                'gradelevels' => $this->gradelevels->datatableAllEditable()->get()
            ]);
    }

    public function show($id)
    {
        $account_id = intval($id);
        return Datatables::of($this->users->datatableForAccount($account_id))
            ->edit_column('teacher_name', '@if($user_type!=3) (n/a) @elseif($teacher_id == 1) (unassigned) @else {{ $teacher_name }} @endif')
            ->edit_column('user_type', '@if($user_type==2) Admin @elseif ($user_type==3) Student @elseif ($user_type==4) Teacher @elseif ($user_type==5) Staff @elseif ($user_type==6) Parent @else None @endif')
            ->edit_column('allowed_to_order', '@if($allowed_to_order==1) Yes @else No @endif')
            ->edit_column('grade_desc', '@if($user_type==2) (n/a) @elseif ($user_type==5) (n/a) @elseif ($user_type==6) (n/a) @elseif($gradelevel_id == 1) (unassigned) @else {{ $grade_desc }} @endif')
            ->make();
    }

    /*****/
    public function teachers()
    {
        return response()->json($this->users->allTeachers());
    }

    /*****/
    public function store(Request $request)
    {
        $inputs = $request->only('user_id', 'account_id', 'first_name', 'last_name', 'user_type', 'grade_id', 'teacher_id', 'allowed_to_order');
        $account_id = intval($inputs['account_id']);
        if ($account_id <= 0)
            return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Invalid Account ID.'));

        $inputs['first_name'] = filter_var($inputs['first_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $inputs['last_name'] = filter_var($inputs['last_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);

        $user_id = intval($inputs['user_id']);
        if ($user_id > 0) {
            $user = User::find($user_id);
            if (is_null($user)) {
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to edit. This user longer exists.'));
            }
        } else {
            $user = new User();
            $user->account_id = $account_id;
            $user->user_type = config('app.user_type_none');
        }

        $user_test = User::where('account_id', $account_id)
            ->where('first_name', $inputs['first_name'])
            ->where('last_name', $inputs['last_name'])
            ->first();

        if ($user_test && $user_id != $user_test->id) {
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => 'User already exists.'));
        }

        $app_type_teacher = config('app.user_type_teacher');

        if ($user->user_type == $app_type_teacher && $inputs['user_type'] != $app_type_teacher) {
            $num_students = User::where('teacher_id', $user_id)->count();
            if ($num_students > 0)
                return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => 'This Teacher has Students assigned. Unable to change user type.'));
        }

        $rules = array(
            'user_id' => array('required', 'integer'),
            'account_id' => array('required', 'integer'),
            'first_name' => array('required', 'min:2', 'max:50'),
            'last_name' => array('required', 'min:2', 'max:50'),
            'user_type' => array('required', 'integer', 'min:2', 'max:6'),
            'grade_id' => array('required', 'integer', 'min:1'),
            'teacher_id' => array('required', 'integer', 'min:1'),
            'allowed_to_order' => array('required', 'integer', 'min:0', 'max:1')
        );

        $validator = Validator::make($inputs, $rules);
        if ($validator->passes()) {
            $user->first_name = $inputs['first_name'];
            $user->last_name = $inputs['last_name'];
            $user->user_type = intval($inputs['user_type']);
            $user->teacher_id = intval($inputs['teacher_id']);
            $user->grade_id = intval($inputs['grade_id']);
            $user->allowed_to_order = intval($inputs['allowed_to_order']);

            if ($user->user_type == $app_type_teacher) {
                $user->teacher_id = config('app.teacher_na_id');
            } else if ($user->user_type != config('app.user_type_student')) {
                $user->teacher_id = config('app.teacher_na_id');
                $user->grade_id = config('app.gradelevel_na_id');
            }

            if ($user->save()) {
                $this->updateAccountPrivilegeLevel($account_id);
                return response()->json(array('error' => false, 'idToFind' => $user->id));
            } else
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to edit User.'));
        } else
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => $validator->messages()->first()));
    }

    /*****/
    private function updateAccountPrivilegeLevel($account_id)
    {
        $account = Account::find($account_id);
        if (!is_null($account)) {
            if (User::where('account_id', $account_id)->where('user_type', config('app.user_type_admin'))->count() > 0)
                $account->privilege_level = config('app.privilege_level_admin');
            else
                $account->privilege_level = config('app.privilege_level_user');
            $account->save();
        }
    }

    /*****/
    public function destroy($id, Request $request)
    {
        $user_id = intval($id);
        $account_id = intval($request->account_id);

        if ($user_id > 0 && $account_id > 0) {
            $num_students = User::where('teacher_id', $user_id)->count();
            if ($num_students > 0) {
                $msg = 'Unable to delete: user is a teacher with ' . $num_students . ' students assigned.';
                return response()->json(array('error' => true, 'msg' => $msg));
            }

            $num_users = User::where('account_id', $account_id)->count();
            if ($num_users < 2) {
                $msg = '<br />To delete the last User for an Account, you must delete the Account.<br /><br />';
                return response()->json(array('error' => true, 'msg' => $msg));
            }

            try {
                User::destroy($user_id);
                $this->updateAccountPrivilegeLevel($account_id);
                return response()->json(array('error' => false));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());

                if ($e->getCode() == 23000) {
                    return response()->json(array('error' => true, 'msg' => 'Unable to delete user: related orders must be removed first.'));
                } else {
                    return response()->json(array('error' => true, 'msg' => $e->getMessage()));
                }
            }
        }
        return response()->json(array('error' => true, 'msg' => 'Invalid account or user ID'));
    }
}
