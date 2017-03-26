<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\AccountRepository;
use Illuminate\Http\Request;


class UtilityController extends Controller
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

    /*****/
    public function index(Request $request)
    {
        return view('admin.utilities.index');
    }

    /*****/
    public function updateAllCreditsDebits(Request $request)
    {
        $this->accounts->updateAllCreditsDebitsFeesOrderCount();
        return redirect('admin/utilities')->with('status', 'Credits and Debits updated successfully.');
    }
}
