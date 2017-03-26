<?php

namespace App\Http\Controllers;

use App\Repositories\AccountRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\UserRepository;

class MyAccountController extends Controller
{
    protected $accounts;
    protected $users;
    protected $orders;
    protected $payments;

    public function __construct(AccountRepository $accounts,
                                UserRepository $users,
                                OrderRepository $orders,
                                PaymentRepository $payments)
    {
        $this->accounts = $accounts;
        $this->users = $users;
        $this->orders = $orders;
        $this->payments = $payments;
    }

    public function getMyAccount($accid)
    {
//        return response()->json(['error' => 'invalid_credentials'], 401);
        $details = new \stdClass();
        $this->accounts->updateCreditsDebitsFeesOrderCount($accid);
        
        //order_count,total_price,first_name,last_name,user_id
        $details->order_aggs = $this->orders->myAccountAggregates($accid);

        //payment_count,credit_amt
        $details->payment_agg = $this->payments->myAccountAggregate($accid);
        $details->fees = $this->payments->myAccountFees($accid);
        $details->cur_balance = $this->accounts->currentBalance($accid);
        return response()->json(['data' => $details]);
    }
}
