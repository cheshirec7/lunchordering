<?php

namespace App\Repositories;

use App\Payment;
use Auth;
use DB;

class PaymentRepository
{
    /**
     * Get payments by account
     *
     * @return Collection
     */
    public function datatableByAccount($account_id)
    {
        return Payment::select('id as DT_RowId', 'pay_method', 'credit_desc', 'credit_amt', DB::raw('DATE_FORMAT(credit_date,"%m/%d/%Y") AS credit_date'))
            ->where('account_id', '=', $account_id)
            ->where('deleted', '=', 0)
            ->where('deleted_at', null)
            ->orderBy('credit_date', 'asc');
    }

    public function datatableMyAccountPayments()
    {
        return Payment::select('id as DT_RowId', 'pay_method', 'credit_desc', 'credit_date', 'credit_amt')
            ->where('account_id', Auth::id())
            ->where('deleted', 0)
            ->where('deleted_at', null)
            ->orderBy('credit_date', 'asc');
    }

    public function myAccountAggregate($account_id)
    {
        return Payment::select(DB::raw('count(id) as payment_count'), DB::raw('COALESCE(sum(credit_amt),0) as credit_amt'))
            ->where('account_id', $account_id)
            ->where('deleted', 0)
            ->where('deleted_at', null)
            ->first();
    }

    public function myAccountFees($account_id)
    {
        return Payment::select(DB::raw('COALESCE(sum(fee),0) as fees'))
            ->where('account_id', $account_id)
            ->where('deleted', 0)
            ->where('deleted_at', null)
            //->where('pay_method', config('app.pay_method_paypal'))
            ->first();
    }

    public function adminAccountDetailReport($account_id)
    {
        return Payment::where('account_id', $account_id)
            ->where('deleted', 0)
            ->where('deleted_at', NULL)
            ->get();
    }
}
