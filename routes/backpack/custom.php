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
    Route::crud('role', 'RoleCrudController');
    Route::crud('permission', 'PermissionCrudController');
    Route::crud('product', 'ProductCrudController');
    Route::crud('transaction', 'TransactionCrudController');
    Route::crud('level', 'LevelCrudController');
    Route::crud('bonus-history', 'BonusHistoryCrudController');
    Route::crud('level-up-histories', 'LevelUpHistoriesCrudController');
}); // this should be the absolute last line of this file