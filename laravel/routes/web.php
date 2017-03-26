<?php

Route::get('/register', function () {
    return redirect('/');
});

Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index');

Route::get('/contact', 'ContactController@getContact');
Route::post('/contact', 'ContactController@postContact');

Route::get('/auth/fb','SocialController@gotoFacebook');
Route::get('/auth/fb/callback', 'SocialController@returnFromFacebook');

Auth::routes();

Route::get('orders/menu', 'OrderController@getMenu');
Route::resource('orders', 'OrderController', ['only' => ['index', 'show', 'store']]);

Route::post('myaccount/pay', 'MyAccountController@pay');
Route::get('myaccount/completepayment', 'MyAccountController@completePayment');
Route::get('myaccount/payments', 'MyAccountController@payments');
Route::get('myaccount/orders/{userid}', 'MyAccountController@orders');
Route::get('myaccount', 'MyAccountController@index');

Route::get('lunchreport', 'ReportController@index');
Route::get('mylunchreport', 'ReportController@doReport');

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::resource('gradelevels', 'Admin\GradeLevelController', ['only' => ['index', 'show', 'store', 'destroy']]);
    Route::resource('nolunchexceptions', 'Admin\NoLunchExceptionController', ['only' => ['index', 'show', 'store', 'destroy']]);
    Route::resource('payments', 'Admin\PaymentController', ['only' => ['index', 'show', 'store', 'destroy']]);
    Route::resource('providers', 'Admin\ProviderController', ['only' => ['index', 'show', 'store', 'destroy']]);
    Route::resource('menuitems', 'Admin\MenuItemController', ['only' => ['index', 'show', 'store', 'destroy']]);

    Route::get('accounts/balance/{account}', 'Admin\AccountController@currentBalance');
    Route::resource('accounts', 'Admin\AccountController', ['only' => ['index', 'show', 'store', 'destroy']]);

    Route::get('users/teachers', 'Admin\UserController@teachers');
    Route::resource('users', 'Admin\UserController', ['only' => ['index', 'show', 'store', 'destroy']]);

    Route::get('schedule/getProviderMenuItems', 'Admin\ScheduleController@getProviderMenuItemsHTML');
    Route::get('schedule/modal', 'Admin\ScheduleController@getModalHTML');
    Route::resource('schedule', 'Admin\ScheduleController', ['only' => ['index', 'show', 'store']]);

    Route::get('ordermaint/transfer', 'Admin\OrderMaintController@getTransferNames');
    Route::post('ordermaint/transfer', 'Admin\OrderMaintController@postTransfer');
    Route::post('ordermaint/lunchdatelocktoggle/{lunchdate_id}', 'Admin\OrderMaintController@postLunchDateLockToggle');
    Route::resource('ordermaint', 'Admin\OrderMaintController', ['only' => ['index', 'show', 'store', 'destroy']]);

    Route::get('utilities', 'Admin\UtilityController@index');
    Route::get('utilities/updateallcreditsdebits', 'Admin\UtilityController@updateAllCreditsDebits');

    Route::get('reports', 'Admin\ReportController@index');
    Route::get('report', 'Admin\ReportController@doReport');
});
