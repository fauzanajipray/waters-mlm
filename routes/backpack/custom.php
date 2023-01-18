<?php

use FontLib\Table\Type\post;
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
    Route::get('member/{id}/download-register', 'MemberCrudController@downloadFormLineRegister');
    Route::get('members', [App\Http\Controllers\Api\MemberController::class, 'index']);
    Route::get('members/not-activated', [App\Http\Controllers\Api\MemberController::class, 'notActivated']);
    Route::get('members/only-actived', [App\Http\Controllers\Api\MemberController::class, 'onlyActive']);
    Route::get('members/not-branch-owner', [App\Http\Controllers\Api\MemberController::class, 'notBranchOwner']);
    Route::get('members/only-nsi', 'MemberCrudController@onlyNsi');
    Route::get('members/branch-owner', 'MemberCrudController@getBranchOwner');
    Route::post('members/for-filter', 'MemberCrudController@getMembersForFilter');
    Route::get('member/register-form', 'MemberCrudController@downloadFormRegister');
    Route::get('member/member-type', 'MemberCrudController@getMemberType');
    Route::crud('role', 'RoleCrudController');
    // Route::crud('permission', 'PermissionCrudController');
    Route::crud('product', 'ProductCrudController');
    Route::prefix('product')->group(function () {
        Route::post('get-product', 'ProductCrudController@getProduct');
        Route::post('get-demokit-products', 'ProductCrudController@getDemokitProducts');
        Route::post('get-display-products', 'ProductCrudController@getDisplayProducts');
        Route::post('get-bebas-putus', 'ProductCrudController@getBebasProducts');
        Route::post('for-filter', 'ProductCrudController@getProductsForFilter');
        Route::post('for-stock', 'ProductCrudController@getProductForStock');
        Route::post('for-transaction', 'ProductCrudController@getProductTransaction');
        Route::post('for-transaction/sparepart', 'ProductCrudController@getProductSparepartTransaction');
        Route::post('for-transaction/stock', 'ProductCrudController@getProductStockTransaction');
        Route::get('{id}/branch/{branch_id}', 'ProductCrudController@getProductStock');
    });
    Route::crud('transaction', 'TransactionCrudController');
    Route::prefix('transaction')->group(function () {
        Route::post('check-customer', 'TransactionCrudController@checkCustomer');
        Route::get('{id}/download-letter-road', 'TransactionCrudController@downloadLetterRoad');
        Route::get('{id}/download-invoice', 'TransactionCrudController@downloadInvoice');
    });
    Route::crud('transaction-display', 'TransactionDisplayCrudController');
    Route::crud('transaction-demokit', 'TransactionDemokitCrudController');
    Route::crud('transaction-bebas-putus', 'TransactionBebasPutusCrudController');
    Route::crud('transaction-sparepart', 'TransactionSparepartCrudController');
    Route::crud('transaction-stock', 'TransactionStockCrudController');
    Route::crud('level', 'LevelCrudController');
    Route::crud('bonus-history', 'BonusHistoryCrudController');
    Route::post('bonus-history/total', 'BonusHistoryCrudController@totalTransactions');
    Route::crud('level-up-histories', 'LevelUpHistoriesCrudController');
    Route::crud('activation-payments', 'ActivationPaymentsCrudController');
    Route::crud('customer', 'CustomerCrudController');
    Route::get('customer/{id}/delete', 'CustomerCrudController@deleteCustomer');
    Route::post('customer/get-customer-by-member-id', 'CustomerCrudController@customerbyMemberID');
    Route::post('customer/get-customer-is-member', 'CustomerCrudController@getCustomerIsMember');
    Route::crud('configuration', 'ConfigurationCrudController');
    Route::crud('branch', 'BranchCrudController');
    Route::get('branch/{id}/delete', 'BranchCrudController@deleteBranch');
    Route::prefix('branches')->group(function (){
        Route::post('/', 'BranchCrudController@getBranches');
        Route::post('member-not-exist', 'BranchCrudController@memberNotExist');
        Route::post('member-exist', 'BranchCrudController@memberExist');
        Route::post('origin', 'BranchCrudController@getOriginBranch');
        Route::post('for-filter', 'BranchCrudController@getBranchesForFilter');
        Route::post('transaction-stock', 'BranchCrudController@getBranchStock');
    });
    Route::crud('payment-method', 'PaymentMethodCrudController');
    Route::crud('transaction-payment', 'TransactionPaymentCrudController');
    Route::crud('stock', 'StockCrudController');
    Route::crud('stock-card', 'StockCardCrudController');
    Route::prefix('stock-card/{idStock}')->group(function () {
        Route::crud('detail', 'StockCardDetailCrudController');
        Route::crud('adjustment', 'StockCardAdjustmentCrudController');
    });
    Route::crud('branch-product', 'BranchProductCrudController');
    Route::crud('level-nsi', 'LevelNsiCrudController');
}); // this should be the absolute last line of this file
