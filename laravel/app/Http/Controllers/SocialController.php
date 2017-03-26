<?php namespace App\Http\Controllers;

use Auth;
use App\Account;
use Socialize;

class SocialController extends Controller
{
    public function gotoFacebook()
    {
        return Socialize::with('facebook')->redirect();
    }

    public function returnFromFacebook(){
        $user = Socialize::with('facebook')->user();

        $account = Account::where('email',$user->email)->first();

        if (! is_null($account)) {
            Auth::loginUsingId($account->id);
            return redirect('/orders');
        }

        $account = Account::where('fb_id',$user->id)->first();

        if (! is_null($account)) {
            Auth::loginUsingId($account->id);
            return redirect('/orders');
        }

        return redirect('/login')->withErrors(
            ['email' => 'Your Facebook account is not linked to a CCA account. Please contact us to create a Facebook / CCA link.']
        );
    }
}