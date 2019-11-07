<?php

Route::match(['get', 'post'], '/paystack/card/add-callback', 'API\PaystackController@addCardCallback')->name('paystack.card.add-callback');

Route::group(['prefix' => 'v1'], function() {

    // AUTH
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', 'API\AuthController@postLogin');
        Route::post('/logout', 'API\AuthController@postLogout')->middleware('user-token-checker');
        Route::post('/forgot-password', 'API\AuthController@postForgotPassword');
        Route::post('/reset-password', 'API\AuthController@postResetPassword');
    });

    // ACCOUNT
    Route::group([
        'prefix' => 'account',
        'middleware' => ['user-token-checker']
    ], function () {
        Route::get('/', 'API\AccountController@getAccount');
        Route::post('/update', 'API\AccountController@updateAccount');
        Route::get('/dashboard', 'API\AccountController@getDashboard');
        Route::get('/checklist', 'API\AccountController@getChecklist');
        Route::post('/device', 'API\AccountController@linkDevice');

        // CARDS
        Route::get('/cards', 'API\AccountCardController@getCards');
        Route::post('/cards/add', 'API\AccountCardController@addCard');
        Route::delete('/cards/{id}', 'API\AccountCardController@deleteCard');
    });

    // MESSAGES
    Route::group([
        'prefix' => 'messages',
        'middleware' => ['user-token-checker']
    ], function () {
        Route::get('/', 'API\AccountMessageController@getMessages');
        Route::get('/{id}/content', 'API\AccountMessageController@getMessageContent')->name('api.v1.messages.content');
    });

    // LOANS
    Route::group([
        'prefix' => 'loans',
        'middleware' => ['user-token-checker']
    ], function () {
        Route::post('/{id}/repayment', 'API\LoanController@loanRepayment');
        Route::post('/{id}/pause', 'API\LoanController@pauseLoan');
        Route::get('/{id}/history', 'API\LoanController@getLoanHistory');
        Route::get('/', 'API\LoanController@getLoans');
        Route::get('/new/init', 'API\LoanController@applicationInit');
        Route::post('/new/calculate', 'API\LoanController@applicationCalculate');
        Route::post('/new/confirm', 'API\LoanController@applicationConfirm');
    });

    // LOCATIONS
    Route::post('/locations/add', 'API\AccountLocationController@addLocation')->middleware('user-token-checker');

    // CONTACTS
    Route::post('/contacts/sync', 'API\AccountContactController@syncContacts')->middleware('user-token-checker');

});


Route::group(['prefix' => 'finance', 'middleware' => ['user-token-checker']], function () {

   Route::post('/add-bank-details', 'APIFinanceController@addBankDetails');

    Route::group(['prefix' => 'payments'], function () {
       // Route::post('/create-transfer-recipient', 'APIFinanceController@createTransferRecipient');
    });

    Route::group(['prefix' => 'card'], function () {
        Route::post('/init', 'APIFinanceController@cardLinkInit');
        Route::get('/verify/{reference}', 'APIFinanceController@cardLinkVerify');
    });
});


Route::group(['prefix' => 'commons', 'middleware' => ['web']], function () {

    Route::get('/loanTypes', 'APILoanTypeController@index');
    Route::get('/banks', 'CommonsController@getBanks');

});


