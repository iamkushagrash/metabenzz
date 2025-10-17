<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/getSponsor/{sponsorid}','Auth\RegisterController@getSponsor');
Route::get('/register/{userid}','Auth\RegisterController@reffer');

Route::get('/Transaction/transactionStatus/{id}','CpsIncomeController@paymentStatus');



//Clear Cache
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('view:clear');
    // return what you want
});


//Admin
Route::group(['middleware' => ['auth','adminverification']], function () {
    Route::get('/Main/Dashboard', 'HomeController@adminindex');


    Route::get('/Main/AdminUSDTuser', 'AccountDepositController@adminUSDTPage');
    Route::post('/Main/AdminUSDTuser', 'AccountDepositController@findUser');
    Route::post('/Main/TransferAdminUSDTToUser', 'AccountDepositController@transferUsdtToAdmin');

    Route::get('/Main/AdminUSDTReport', 'AccountDepositController@reportAdminUsdt');
    Route::post('/Main/AdminUSDTReport', 'AccountDepositController@reportAdminUsdt');
    Route::get('/Main/UserWalletBalance', 'AccountDepositController@userWalletBalance');



});




Route::group(['middleware' => ['auth','userverification']], function () {
    Route::get('/User/Dashboard', 'HomeController@userindex');
    Route::get('/User/Documentation', 'HomeController@documentation');
    // Route::get('/User/ArbitrageDashboard', 'HomeController@userarbitrageindex');
    Route::get('/User/EditProfile', 'UserDetailsController@showEditData');
    Route::post('/User/EditProfile', 'UserDetailsController@userUpdate');
    // Route::get('/User/resendProfileOtp','AssetDetailChangesController@resendProfileEditOtpWeb');

    Route::get('/User/ChangePassword', 'UserDetailsController@showChangePass');
    Route::post('/User/ChangePassword', 'UserDetailsController@UserChangePass');
    
    Route::get('/User/DirectTeam', 'UserDetailsController@getDirect');
    Route::get('/User/AllTeam', 'UserDetailsController@getTotal');
    Route::get('/User/TeamSummary', 'UserDetailsController@userTeamSummary');
    Route::get('/User/Treeview', 'UserDetailsController@getTreeView');

    Route::get('/User/NewRegistration', 'AssetDetailController@userNewRegistrationPage');
    Route::post('/User/NewRegistration', 'AssetDetailController@userNewRegistration');
    Route::get('/getSponsorNew/{sponsorid}','AssetDetailController@getSponsor');

     // Transaction using NP
    Route::get('/User/Deposit','CpsIncomeController@showPage');
    Route::post('/User/Deposit','CpsIncomeController@submitTransaction');

      // Deposit History
    Route::get('/User/DepositHistory', 'BonusRewardController@userDepositHistory');

        // Dragging 
    Route::get('/User/Drag', 'WalletTransferController@dragpage');
    Route::post('/User/getUser', 'WalletTransferController@getUserDetail');
    Route::post('/User/Drag', 'WalletTransferController@dragmbz');
    Route::get('/User/DraggingHistory', 'BonusRewardController@userUpgradeHistory');
    Route::get('/User/DraggingTxnHistory', 'BonusRewardController@userUpgradeTxnHistory');
    
       // Locking 
    Route::get('/User/Lock', 'WalletTransferController@lockPage');
    // Route::post('/User/getUser', 'WalletTransferController@getUserDetail');
    Route::post('/User/Lock', 'WalletTransferController@lockmbz');
     Route::post('/User/getUserforlock', 'WalletTransferController@getUserDetailforlock');
    Route::get('/User/LockingHistory', 'BonusRewardController@userUpgradeLockingHistory');
    Route::get('/User/LockingTxnHistory', 'BonusRewardController@userUpgradeTxnHistory');

     //Support
    Route::post('/User/CreateTicket','SupportQueryController@UserCreateTicket');
    Route::get('/User/ViewTicket','SupportQueryController@viewUserTicket');
    Route::get('/User/TicketView/{title}/{id}','SupportQueryController@viewTicketSingleUser');
    Route::post('/User/ReplyTicket','SupportQueryController@postReplyUser');

});
