<?php

namespace App\Http\Controllers;

use Jenssegers\Agent\Agent;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application home page
     */
    public function index()
    {
        $agent = new Agent();
        return view('welcome', ['isPhone' => $agent->isPhone()]);
    }
}
