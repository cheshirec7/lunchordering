<?php

namespace App\Repositories;

use App\Account;
use DB;

class AccountRepository
{

    /**
     * left join to get all accounts even if no users are attached (which should never happen...)
     * $accounts = Account::select(array('accountname', 'email', 'total_users', 'total_orders', 'confirmed_credits', 'total_debits', 'los_accounts.id as id'))
     * ->where('account_type', '>', config('app.account_type_super'));
     * @param  Account $account
     * @return Collection
     */
    public function datatableAllExceptSuperAndDefaultWithUsercount()
    {
        return Account::select('los_accounts.id as DT_RowId', 'account_name', 'email',
            DB::raw('COUNT(los_users.id) as usercount'), 'total_orders', 'confirmed_credits', 'total_debits', 'fb_id')
            ->leftjoin('los_users', 'los_users.account_id', '=', 'los_accounts.id')
            ->where('los_accounts.privilege_level', '<', config('app.privilege_level_super'))
            ->where('los_accounts.id', '>', config('app.default_account_id'))
            ->groupBy('DT_RowId')
            ->groupBy('account_name')
            ->groupBy('email')
            ->groupBy('total_orders')
            ->groupBy('confirmed_credits')
            ->groupBy('total_debits')
            ->groupBy('fb_id')
            ->orderBy('account_name', 'asc');
    }

    public function allEditableAcounts()
    {
        return Account::select('los_accounts.id', 'account_name')
            ->where('los_accounts.privilege_level', '<', config('app.privilege_level_super'))
            ->where('los_accounts.id', '>', config('app.default_account_id'))
            ->orderBy('account_name', 'asc');
    }

    public function activeAccounts()
    {
        return Account::select('id', 'account_name')
            ->where('total_orders', '>', 0)
            ->orderBy('account_name', 'asc')
            ->get();
    }

    public function adminAccountBalancesReport()
    {
        return Account::where('total_orders', '>', 0)
            ->orderBy('account_name', 'asc')
            ->get();
    }

    public function adminAccountDetailReport($account_id)
    {
        return Account::where('id', $account_id)->first();
    }

    public function updateAllCreditsDebitsFeesOrderCount()
    {
        //update los_payments set fee=credit_amt-(credit_amt/1.022) where pay_method=3
        DB::statement('UPDATE los_accounts a SET confirmed_credits=(SELECT COALESCE(SUM(credit_amt),0) FROM los_payments p WHERE a.id = p.account_id AND deleted_at IS NULL AND deleted=0)');
        DB::statement('UPDATE los_accounts a SET fees=(SELECT COALESCE(SUM(fee),0) FROM los_payments p WHERE a.id=p.account_id AND deleted_at IS NULL AND deleted=0)');
        DB::statement('UPDATE los_accounts a SET total_debits=(SELECT COALESCE(SUM(total_price),0) FROM los_orders o WHERE a.id = o.account_id AND o.status_code < 2)');
        DB::statement('UPDATE los_accounts a SET confirmed_debits=(SELECT COALESCE(SUM(total_price),0) FROM los_orders o WHERE a.id = o.account_id AND o.status_code = 1)');
        DB::statement('UPDATE los_accounts a SET total_orders=(SELECT COALESCE(SUM(qty),0) FROM los_orderdetails o WHERE a.id = o.account_id)');
    }

    public function updateAccountCredits($account_id)
    {
        DB::statement('UPDATE los_accounts a SET confirmed_credits=(SELECT COALESCE(SUM(credit_amt),0) FROM los_payments p WHERE p.account_id=:accid_1 AND deleted=0 AND deleted_at IS NULL) WHERE a.id = :accid_2',
            array('accid_1' => $account_id, 'accid_2' => $account_id));
    }

    public function amountDue($account_id, $updateAggregates)
    {
        if ($updateAggregates)
            $this->updateCreditsDebitsFeesOrderCount($account_id);

        $bal = 0;
        $account = Account::select('total_debits', 'confirmed_credits', 'fees')
            ->where('id', $account_id)
            ->first();
        if ($account)
            if ($account->confirmed_credits > $account->total_debits)
                $bal = $account->confirmed_credits - $account->fees - $account->total_debits;
            else
                $bal = -($account->total_debits - ($account->confirmed_credits - $account->fees));

        if (abs($bal) < 5)
            $bal = 0;

        if ($bal < 0)
            return '$' . number_format(-$bal / 100, 2);
        else
            return '$0.00';
    }

    public function updateCreditsDebitsFeesOrderCount($account_id)
    {
        DB::statement('UPDATE los_accounts a SET confirmed_credits=(SELECT COALESCE(SUM(credit_amt),0) FROM los_payments p WHERE p.account_id=' . $account_id . ' AND deleted_at IS NULL and deleted=0) WHERE a.id=' . $account_id);
        DB::statement('UPDATE los_accounts a SET fees=(SELECT COALESCE(SUM(fee),0) FROM los_payments p WHERE p.account_id=' . $account_id . ' AND deleted_at IS NULL AND deleted=0) WHERE a.id=' . $account_id);
        DB::statement('UPDATE los_accounts a SET total_debits=(SELECT COALESCE(SUM(total_price),0) FROM los_orders o WHERE o.account_id=' . $account_id . ' AND o.status_code < 2) WHERE a.id=' . $account_id);
        DB::statement('UPDATE los_accounts a SET confirmed_debits=(SELECT COALESCE(SUM(total_price),0) FROM los_orders o WHERE o.account_id=' . $account_id . ' AND o.status_code = 1) WHERE a.id=' . $account_id);
        DB::statement('UPDATE los_accounts a SET total_orders=(SELECT COALESCE(SUM(qty),0) FROM los_orderdetails o WHERE o.account_id=' . $account_id . ') WHERE a.id=' . $account_id);
        return $this->currentBalance($account_id);
    }

    //returned string will always be a positive formatted currency

    public function currentBalance($account_id)
    {
        $account = Account::select('total_debits', 'confirmed_credits', 'fees')
            ->where('id', $account_id)
            ->first();
        if ($account)
            //returned integer can be positive (an account credit) or negative (balance due)
            return round($account->confirmed_credits - $account->fees - $account->total_debits);
        else
            return 0;
    }
}
