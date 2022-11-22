<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('member', 'MemberCrudController');
    Route::get('member/{id}/download-card-member', 'MemberCrudController@downloadCardMember');
    Route::get('member/{id}/report-member', 'MemberCrudController@reportMember');
    Route::get('/members', [App\Http\Controllers\Api\MemberController::class, 'index']);
    Route::get('/members/not-activated', [App\Http\Controllers\Api\MemberController::class, 'notActivated']);
    Route::get('/members/only-actived', [App\Http\Controllers\Api\MemberController::class, 'onlyActive']);
    Route::crud('role', 'RoleCrudController');
    Route::crud('permission', 'PermissionCrudController');
    Route::crud('product', 'ProductCrudController');
    Route::crud('transaction', 'TransactionCrudController');
    Route::post('transaction/check-customer', 'TransactionCrudController@checkCustomer');
    Route::get('transaction/{id}/download-letter-road', 'TransactionCrudController@downloadLetterRoad');
    Route::get('transaction/{id}/download-invoice', 'TransactionCrudController@downloadInvoice');
    Route::crud('level', 'LevelCrudController');
    Route::crud('bonus-history', 'BonusHistoryCrudController');
    Route::crud('level-up-histories', 'LevelUpHistoriesCrudController');
    Route::crud('activation-payments', 'ActivationPaymentsCrudController');
    Route::crud('customer', 'CustomerCrudController');
    Route::post('customer/get-customer-by-member-id', 'CustomerCrudController@customerbyMemberID');
    Route::post('customer/get-customer-is-member', 'CustomerCrudController@getCustomerIsMember');
}); // this should be the absolute last line of this file