<?php

$app->get('/', function () use ($app) {
//    return view('home');
  return $app->version();
});

// \Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
//     var_dump($query->sql);
//     var_dump($query->bindings);
//     var_dump($query->time);
// });

/* @var $api Dingo\Api\Routing\Router */
$api = app('Dingo\Api\Routing\Router');

//$api->version('v1', ['middleware' => 'cors', 'namespace' => 'App\Http\Controllers'], function ($api) {
$api->version('v1', ['namespace' => 'App\Http\Controllers'], function ($api) {
  // All routes in this callback are prefixed by "/api" (change in .env)

  // Authentication routes
  $api->post('auth', 'AuthController@login');
  $api->get('auth', 'AuthController@verify');
  $api->delete('auth', 'AuthController@destroy');
});

//$api->version('v1', ['middleware' => ['cors','api.auth'], 'namespace' => 'App\Http\Controllers'], function ($api) {
$api->version('v1', ['middleware' => ['api.auth'], 'namespace' => 'App\Http\Controllers'], function ($api) {

  // The resource call creates these routes:
  // GET	        /api/users              index   users.index     Display a listing of the resource
  // POST	        /api/users              store	users.store     Store a newly created resource in storage
  // GET	        /api/users/{id}         show	users.show      Display the specified resource
  // PUT/PATCH	/api/users/{id}         update	users.update    Update the specified resource in storage
  // DELETE	    /api/users/{id}         destroy	users.destroy   Remove the specified resource from storage

  // GET	        /api/users/create       create	users.create    Show the form for creating a new resource
  // GET	        /api/users/{id}/edit    edit	users.edit      Show the form for editing the specified resource

  // $api->resource('users', 'UserController');

  $api->get('orders/{accid}/{period}', 'OrderController@getAccountForPeriod');
  $api->post('orders', 'OrderController@store');
  $api->get('menu/{ts}/{uid}', 'OrderController@getMenu');
  $api->get('myaccount/{accid}', 'MyAccountController@getMyAccount');

});


$app->get('{slug:.*}', 'AngularController@serve');