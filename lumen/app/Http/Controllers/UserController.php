<?php

namespace App\Http\Controllers;

use App\User;
//use App\Http\Controllers\Controller;
//use App\Http\Requests;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json( ['data' => User::select('id', 'account_name', 'email', 'enabled')->take(5)->get()] );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response()->json( ['success' => 'user_created'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if ($user)
            return response()->json( ['data' => $user] );
        else
            return response()->json( ['error' => 'user_not_found'], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //$user = User::find($id);
        //$user->name = 'New Name';
        //$user->save();
        return response()->json( ['success' => 'user_updated'], 204); //or 200
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $user = User::find($id);
        // $user->delete();
        // or
        // User::destroy($id);
        return response()->json( ['success' => 'user_deleted'], 204); //or 200
    }


    ////////////////////////////////////////////////////////////////////////////
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
}
