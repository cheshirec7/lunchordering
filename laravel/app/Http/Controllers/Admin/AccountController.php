<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Repositories\AccountRepository;
use App\User;
use Datatables;
use Illuminate\Http\Request;
use Validator;

class AccountController extends Controller
{

    protected $accounts;

    /**
     * Create a new controller instance.
     *
     * @param  AccountRepository $accounts
     */
    public function __construct(AccountRepository $accounts)
    {
        $this->accounts = $accounts;
    }

    /**
     * Display a list of all of the account's orders.
     *
     * @param  Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('admin.accounts.index');
    }

    public function currentBalance($id)
    {
        return response()->json(array('bal' => $this->accounts->currentBalance($id)));
    }

    public function show($id)
    {
        $accounts = $this->accounts->datatableAllExceptSuperAndDefaultWithUsercount();

        return Datatables::of($accounts)
            ->edit_column('confirmed_credits', '${{ number_format($confirmed_credits/100,2) }}')
            ->edit_column('total_debits', '${{ number_format($total_debits/100,2) }}')
            ->make();
    }

    /**
     * Create a new account.
     *
     * @param  Request $request
     * @return Response
     */
    //INSERT INTO `los_accounts` (`id`, `username`, `accountname`, `email`, `password`, `confirmation_code`, `remember_token`, `confirmed`, `account_type`, `confirmed_credits`, `confirmed_debits`, `total_debits`, `total_orders`, `created_at`, `updated_at`) VALUES
    //(1, '(unassigned)', '(unassigned)', '(unassigned)', '(unassigned)', NULL, NULL, 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
    //(2, 'superuser', 'superuser', 'erictotten@cox.net', 'pwd', NULL, 'token', 1, 1, 0, 0, 0, 0, '0000-00-00 00:00:00', '2014-09-16 12:33:34'),
    //INSERT INTO `los_users` (`id`, `account_id`, `first_name`, `last_name`, `allowed_to_order`, `teacher_id`, `grade_id`, `user_type`, `created_at`, `updated_at`) VALUES
    //(1, 1, '(unassigned)', '(unassigned)', 0, 2, 2, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
    //(2, 1, '(n/a)', '(n/a)', 0, 2, 2, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
    //(3, 2, 'Eric', 'Totten', 0, 2, 2, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00')
    public function store(Request $request)
    {
        $inputs = $request->only('account_id', 'account_name', 'email', 'fbuserid');
        $inputs['account_name'] = filter_var($inputs['account_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $inputs['email'] = filter_var($inputs['email'], FILTER_SANITIZE_EMAIL);

        $account_id = intval($inputs['account_id']);

        if ($account_id > 0) {
            $rules = [
                'account_name' => 'required|unique:los_accounts,account_name,' . $account_id,
                'email' => 'required|email|unique:los_accounts,email,' . $account_id,
            ];
        } elseif ($account_id == 0) {
            $rules = [
                'account_name' => 'required|unique:los_accounts,account_name',
                'email' => 'required|email|unique:los_accounts,email',
            ];
        } else {
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => 'Invalid account id.'));
        }

        $validator = Validator::make($inputs, $rules);

        if ($validator->passes()) {

            if ($account_id > 0) {
                $account = Account::find($account_id);
                if (!$account)
                    return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to edit. This account longer exists.'));

                $account->account_name = $inputs['account_name'];
                $account->email = $inputs['email'];
                $account->fb_id = $inputs['fbuserid'];
                //$account->username = $inputs['username'];
            } else {
                $account = new Account();
                $account->account_name = $inputs['account_name'];
                $account->email = $inputs['email'];
                //$account->username = $inputs['username'];
                $account->password = bcrypt($account->email);
                $account->fb_id = $inputs['fbuserid'];
            }

            if ($account->save()) {
                if ($account_id > 0)
                    return response()->json(array('error' => false, 'idToFind' => $account->id, 'email' => $account->email));

                $user = new User();
                $user->account_id = $account->id;
                $user->first_name = '[firstname]';
                $last_name = explode(' ', $account->account_name);
                $user->last_name = rtrim($last_name[0], ',');
                if ($user->save())
                    return response()->json(array('error' => false, 'idToFind' => $account->id, 'email' => $account->email));
                else
                    return response()->json(array('error' => true, 'msg' => 'Unable to create User.'));

            } else
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => $account->errors()->first()));
        } else
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => $validator->messages()->first()));
    }

    /**
     * Destroy the given order.
     *
     * @param  Request $request
     * @param  Accounts $account
     * @return Response
     */
    public function destroy($id)
    {
        //SELECT count(u1.id) as teachercount
        //FROM users u
        //INNER JOIN users u1 ON u.id=u1.teacherID
        //WHERE u.account_id=104
        //DB::table('users')

        $account_id = intval($id);

        if ($account_id > 0) {
            $students_assigned = User::join('los_users as teachers', 'los_users.id', '=', 'teachers.teacher_id')
                ->where('los_users.account_id', $account_id)
                ->count();
            if ($students_assigned > 0) {
                $msg = 'This Account has ' . $students_assigned . ' students assigned to a teacher, and cannot be deleted until all students are reassigned.';
                return response()->json(array('error' => true, 'msg' => $msg));
            }

            /*$orders_assigned = Order::where('account_id', $account_id)->count();
            if ($orders_assigned > 0) {
                $msg = '<br /><br />This account has lunch orders assigned.<br /><br />The account cannot be deleted until all lunch orders are deleted.<br /><br />';
                return response()->json(array('error' => true, 'msg' => $msg));
            }*/

            //if (config('app.debug') == false) {
            //DB::table('orderdetails')->where('account_id',$account_id)->delete();
            //DB::table('orders')->where('account_id',$account_id)->delete();
            //DB::table('payments')->where('account_id',$account_id)->delete();
            //OrderDetail::where('account_id', $account_id)->delete();
            //Order::where('account_id', $account_id)->delete();
            //Payment::where('account_id', $account_id)->delete();
            //User::where('account_id', $account_id)->delete();

            try {
                Account::destroy($account_id);
                return response()->json(array('error' => false));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());

                if ($e->getCode() == 23000) {
                    return response()->json(array('error' => true, 'msg' => 'Unable to delete account: related orders and payments must be removed first.'));
                } else {
                    return response()->json(array('error' => true, 'msg' => $e->getMessage()));
                }
            }
        }
        return response()->json(array('error' => true, 'msg' => 'Invalid account id'));
    }
}
