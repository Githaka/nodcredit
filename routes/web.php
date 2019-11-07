<?php

Route::get('/v2', 'FrontendController@getHome')->name('frontend.home');
Route::get('/v2/term-conditions', 'FrontendController@getTerms')->name('frontend.terms');
Route::get('/v2/privacy-policy', 'FrontendController@getPolicy')->name('frontend.policy');
Route::get('/v2/invest', 'FrontendController@getInvest')->name('frontend.invest');
Route::get('/v2/invest/start', 'FrontendController@getInvestStart')->name('frontend.invest.start');
Route::get('/v2/invest/info', 'FrontendController@getInvestInfo')->name('frontend.invest.info');
Route::get('/v2/loan/start', 'FrontendController@getLoanStart')->name('frontend.loan.start');
Route::get('/v2/loan/info', 'FrontendController@getLoanInfo')->name('frontend.loan.info');

// Register
Route::post('/v2/auth/register/customer', 'AuthController@postRegisterCustomer')->name('auth.register.customer');
Route::post('/v2/auth/register/investor', 'AuthController@postRegisterInvestor')->name('auth.register.investor');

// Login
// TODO: enable route names after activating v2
Route::get('/v2/auth/login', 'AuthController@getLogin')->name('auth.login');
Route::post('/v2/auth/login', 'AuthController@postLogin');
Route::get('/v2/auth/forgot-password', 'AuthController@getForgotPassword')/*->name('auth.forgot-password')*/;
Route::post('/v2/auth/forgot-password', 'AuthController@postForgotPassword')/*->name('auth.forgot-password')*/;
Route::get('/v2/auth/reset-password/{token}', 'AuthController@getResetPassword')/*->name('auth.reset-password')*/;
Route::post('/v2/auth/reset-password', 'AuthController@postResetPassword')/*->name('auth.reset-password')*/;


// Account phone verify
Route::group([
    'middleware' => 'auth'
], function() {
    Route::get('/v2/auth/phone/verify', 'AuthController@getPhoneVerify')->name('auth.phone.verify');
    Route::get('/v2/auth/phone/verify/resend', 'AuthController@getPhoneVerifyResend')->name('auth.phone.verify.resend');
    Route::post('/v2/auth/phone/verify', 'AuthController@postPhoneVerify')->name('auth.phone.verify');
});


Route::get('/', function () {
    return view('frontend.index');
})->name('home');

Route::get('/faq', function () {
    return view('frontend.faq');
});

Route::get('/privacy-policy', function () {
    return view('frontend.privacy-policy');
});

Route::get('/term-conditions', function () {
    return view('frontend.term-conditions');
});

Route::get('/loan-eligibility', function() {
    return view('frontend.loan-eligibility');
});

Route::get('/build-trust', function() {
    return view('frontend.build-trust');
});

Route::get('/invest', function() {
    return view('frontend.invest');
});


Route::get('/login', 'UiAuthController@login')->name('login');
Route::get('/verify-mobile', 'UiAuthController@verifyMobile')->name('verify.mobile');
Route::post('/verify-mobile', 'UiAuthController@verifyMobilePost')->name('verify.mobile.post');
Route::post('/login', 'UiAuthController@processLogin')->name('ui.auth.login.process');
Route::get('/logout', 'UiAccountController@logout')->name('account.logout');
Route::get('/register', 'UiAuthController@register')->name('ui.auth.register');
Route::post('/register', 'UiAuthController@registerProcess')->name('ui.auth.register.process');
Route::get('/get-loan', 'UiAuthController@getLoan')->name('get-loan');

Route::get('/forgot-password', 'PasswordResetController@forgotPassword')->name('auth.forgot-password');
Route::post('/forgot-password', 'PasswordResetController@forgotPasswordPost')->name('auth.forgot-password-post');
Route::get('reset-password/{token}', 'PasswordResetController@resetPassword')->name('auth.reset-password');
Route::post('reset-password/{any}', 'PasswordResetController@setNewPassword')->name('auth.reset-password-process');
Route::get('/verify-email/{token}', 'PasswordResetController@verifyEmail')->name('auth.verify-email');
Route::get('/resend-email-verification', 'PasswordResetController@resendEmailVerification')->name('auth.resend-email-verification');

Route::post('/account/me/full-loan-application', 'UiAccountController@processWizardLoanForm')->name('account.wizard.loan.form');


Route::get('/amount-growth-graph/{amount}/{days}', 'CommonsController@getAmountGrowthGraph')->name('amount-growth-graph');

// Loan handling confirmation
Route::get('/loan/handling-confirmation/{token}/confirm', 'AccountLoanController@getLoanHandlingConfirm')->name('loan.handling-confirmation.confirm');
Route::get('/loan/handling-confirmation/{token}/reject', 'AccountLoanController@getLoanHandlingReject')->name('loan.handling-confirmation.reject');


Route::group(['prefix' => 'account', 'middleware' => ['auth', 'force-change-pwd']], function(){

    Route::group([
        //TODO: enable middleware after activating v2
        'middleware' => ['user-is-active'/*, 'user-phone-is-verified'*/]
    ], function() {
        Route::get('/', 'UiAccountController@index')->name('account.home');
        Route::get('/me', 'UiAccountController@profile')->name('account.profile');
        Route::post('/me', 'UiAccountController@profileProcess')->name('account.profile.update');
        Route::get('/me/invest', 'UiAccountController@invest')->name('account.profile.invest');
        Route::get('/me/invest/liquidate/{id}', 'UiAccountController@liquidate')->name('account.profile.liquidate');
        Route::post('/me/invest/liquidate/{id}', 'UiAccountController@liquidateProcess')->name('account.profile.liquidate.process');
        Route::get('/me/work-history', 'UiAccountController@loadWorkHistory');
        Route::post('/me/work-history', 'UiAccountController@storeWorkHistory');
        Route::delete('/me/work-history/{id}', 'UiAccountController@deleteWorkHistory');
        Route::get('/me/apply/init', 'UiAccountController@applyForLoanInit')->name('account.profile.apply.init');
        Route::post('/me/apply/init', 'UiAccountController@applyForLoanInitPost')->name('account.profile.apply.init.post');
        Route::post('/me/apply/init-recalculate', 'UiAccountController@applyForLoanInitRecalculate')->name('account.profile.apply.init.recalculate');
        Route::post('/me/apply/create', 'UiAccountController@applyForLoanCreatePost')->name('account.profile.apply.init.create');
        Route::get('/me/apply', 'UiAccountController@applyForLoan')->name('account.profile.apply');
        Route::post('/me/apply', 'UiAccountController@applyForLoanStore')->name('account.profile.apply.store');
        Route::get('/me/card/paystack-callback', 'UiAccountController@checkpaystackTransaction')->name('account.card.paystack.callback');
        Route::post('/me/update-bank', 'UiAccountController@profileUpdateBank')->name('account.profile.update.bank');
        Route::get('/me/loans', 'UiAccountController@loans')->name('account.loans');
        Route::get('/me/loans/{id}', 'UiAccountController@viewLoan')->name('account.loans.show');
        Route::post('/me/loans/{id}/document-upload', 'UiAccountController@uploadLoanDocument')->name('account.loans.upload-document');
        Route::get('/me/loans/{id}/amount-confirm', 'AccountLoanController@getAllowedAmountConfirm')->name('account.loans.amount-confirm');
        Route::get('/me/loans/{id}/amount-confirm-manually', 'AccountLoanController@getNewAmountConfirmManually')->name('account.loans.amount-confirm-manually');
        Route::get('/me/loans/{id}/amount-reject', 'AccountLoanController@getAllowedAmountReject')->name('account.loans.amount-reject');
        Route::get('/me/loans/{id}/prev-loan-amount-confirm', 'AccountLoanController@getPrevLoanAmountConfirm')->name('account.loans.prev-loan-amount-confirm');

        Route::get('/me/loan-repayment', 'UiAccountController@loanRepayment')->name('account.loans.repayment');
        Route::post('/me/loan-repayment', 'UiAccountController@handlePayNow')->name('account.loans.repayment.bill');
        Route::post('/me/loan-repayment/pause-payment', 'UiAccountController@postLoanPause')->name('account.loans.repayment.pause-payment');
        Route::post('/me/loan-repayment/{id}', 'UiAccountController@loanRepaymentSave')->name('account.loans.repayment.save');
        Route::get('/change-password', 'UiAccountController@changePassword')->name('user.change.password');
        Route::post('/change-password', 'UiAccountController@changePasswordStore')->name('user.change.password.store');

        Route::get('/me/downloads', 'UiAccountController@getDownloads')->name('account.downloads');
        Route::get('/me/downloads/app-install/skip', 'UiAccountController@getAppInstallSkip')->name('account.downloads.app-install.skip');

        Route::get('/investments', 'AccountInvestmentController@getInvestments')->name('account.investments');
        Route::get('/investments/{id}', 'AccountInvestmentController@getInvestment')->name('account.investments.investment');
        Route::post('/investments/{id}/liquidate', 'AccountInvestmentController@postInvestmentLiquidate')->name('account.investments.investment.liquidate');

    });

    Route::get('/me/suspended', 'UiAccountController@getSuspended')->name('account.suspended');


    Route::get('/loan-ranges', 'UiAdminLoanRangeController@index')->name('admin.loan-ranges');
    Route::post('/loan-ranges', 'UiAdminLoanRangeController@store')->name('admin.loan-range.store');

    Route::post('/card/init', 'UiAccountController@cardLinkInit');



});

Route::group(['prefix' => 'mainframe', 'middleware' => ['auth']], function(){

    // Protect by admin level
    Route::group([
        'middleware' => 'role:admin',
    ], function() {
        Route::get('/', 'UiAdminLoanController@dashboard')->name('mainframe.dashboard');
        Route::get('/loans', 'UiAdminLoanController@loans')->name('mainframe.loans');
        Route::get('/loans/download', 'UiAdminLoanController@downloadLoans')->name('mainframe.loans.download');
        Route::get('/loans/{id}', 'UiAdminLoanController@viewLoan')->name('mainframe.loans.show');
        Route::get('/loans/{id}/payments', 'UiAdminLoanController@loanPayments')->name('mainframe.loans.payments');
        Route::post('/loans/{id}/payments', 'UiAdminLoanController@transferPayments')->name('mainframe.loans.payments.transfer');
        Route::get('/loans/{id}/approval', 'UiAdminLoanController@loanApproval')->name('mainframe.loans.approval');
        Route::post('/loans/{id}/approval', 'UiAdminLoanController@loanApprovalStore')->name('mainframe.loans.approval.store');
        Route::get('/loans/{id}/json', 'UiAdminLoanController@getLoanJson')->name('mainframe.loans.json');
        Route::post('/loans/{id}/send-new-amount', 'UiAdminLoanController@postSendNewAmount')->name('mainframe.loans.send-new-amount');

        Route::post('/payments/{id}/parts/add', 'UiAdminLoanController@postPartPaymentAdd');
        Route::post('/payments/{id}/increase-amount', 'UiAdminLoanController@postPaymentIncreaseAmount');

        Route::get('investments', 'AdminInvestmentController@getIndex')->name('mainframe.investments');
        Route::get('investments/{id}/start', 'AdminInvestmentController@getInvestmentStart')->name('mainframe.investments.start');
        Route::get('investments/add', 'AdminInvestmentController@getInvestmentAdd')->name('mainframe.investments.add');
        Route::post('investments/add', 'AdminInvestmentController@postInvestmentAdd')->name('mainframe.investments.add');
        Route::get('investments/{id}/manage', 'AdminInvestmentController@getInvestmentManage')->name('mainframe.investment.manage');
        Route::post('investments/{id}/start/edit', 'AdminInvestmentController@postInvestmentStartEdit')->name('mainframe.investment.start.edit');
        Route::post('investments/{id}/edit', 'AdminInvestmentController@postInvestmentEdit')->name('mainframe.investment.edit');
        Route::post('investments/{id}/withholding-tax/edit', 'AdminInvestmentController@postInvestmentWithholdingTaxEdit')->name('mainframe.investment.withholding-tax.edit');
        Route::get('investments/{id}', 'AdminInvestmentController@getInvestment')->name('mainframe.investment');
        Route::get('investments/profit-payments/{id}/auto-payout/edit', 'AdminInvestmentController@postProfitPaymentAutoPayout');
        Route::post('investments/profit-payments/{id}/auto-payout/edit', 'AdminInvestmentController@postProfitPaymentAutoPayout');
        Route::get('investments/profit-payments/{id}/payout', 'AdminInvestmentController@getProfitPaymentPayout');
        Route::get('investments/partial-liquidations/{id}/payout', 'AdminInvestmentController@getPartialLiquidationPayout');

        Route::post('accounts/investor/add', 'UiAdminAccountsController@postInvestorAdd')->name('mainframe.accounts.investor.add');

        Route::group(['prefix' => 'settings'], function(){
            Route::get('/', 'UiAdminSettingsController@index')->name('admin.settings');
            Route::post('/', 'UiAdminSettingsController@store')->name('admin.settings.store');
            Route::get('/score-config', 'UiScoreConfigController@index')->name('admin.score-config');
            Route::post('/score-config', 'UiScoreConfigController@store')->name('admin.score-config.store');
        });

        Route::get('/message-templates', 'UiAdminMessageTemplateController@getIndex')->name('admin.message-templates');
        Route::get('/message-templates/{id}', 'UiAdminMessageTemplateController@getEdit')->name('admin.message-templates.edit');
        Route::post('/message-templates/{id}', 'UiAdminMessageTemplateController@postStore')->name('admin.message-templates.store');

        Route::group(['prefix' => 'commons'], function(){

            Route::get('/document-types', 'UiAdminCommonsController@loanDocumentType')->name('admin.commons.loan.doc-type');
            Route::post('/document-types', 'UiAdminCommonsController@loanDocumentTypeStore')->name('admin.commons.loan.doc-type.store');
        });

        Route::post('/disbursed-and-repayment-chart', 'UiAdminController@postDisbursedAndRepaymentChart');
        Route::get('/customers-charts', 'UiAdminController@getCustomersCharts');
    });

    // Protect by admin or support level
    Route::group([
        'middleware' => 'role:admin|support',
    ], function() {
        Route::get('/payments', 'UiAdminLoanController@payments')->name('mainframe.payments');
        Route::get('/payments/download', 'UiAdminLoanController@downloadPayments')->name('mainframe.payments.download');
        Route::get('/payments/{id}', 'UiAdminLoanController@showPayment')->name('mainframe.payments.show');
        Route::post('/payments/{id}/set-duedate', 'UiAdminLoanController@setDueDate')->name('account.loans.payments.set-payment-date');

        Route::post('/payments/{id}/pause-penalty', 'UiAdminLoanController@postPaymentPausePenalty');
        Route::get('/payments/{id}/parts', 'UiAdminLoanController@getPartPayments');

        Route::get('/loans-documents/{id}/download', 'UiAdminLoanController@getLoanDocumentDownload')->name('account.loan-documents.download');

        Route::group(['prefix' => 'accounts'], function(){
            Route::get('/', 'UiAdminAccountsController@accounts')->name('admin.accounts');
            Route::get('/banned', 'UiAdminAccountsController@getBannedAccounts')->name('admin.accounts.banned');
            Route::get('/download', 'UiAdminAccountsController@downloadAccounts')->name('admin.accounts.download');
            Route::get('/shadow/{id}', 'UiAdminAccountsController@shadowAccount')->name('admin.accounts.shadow');
            Route::get('/{id}/show', 'UiAdminAccountsController@showAccount')->name('admin.accounts.show');
            Route::get('/{id}/message', 'UiAdminAccountsController@message')->name('admin.accounts.message');
            Route::post('/{id}/message', 'UiAdminAccountsController@sendMessage')->name('admin.accounts.message.send');
            Route::get('/{id}/change-password', 'UiAdminAccountsController@changePassword')->name('admin.change.password');
            Route::post('/{id}/change-password', 'UiAdminAccountsController@changePasswordStore')->name('admin.change.password.store');
            Route::get('/{id}/contacts', 'UiAdminAccountsController@getAccountContacts')->name('admin.accounts.contacts');
            Route::get('/{id}/locations', 'UiAdminAccountsController@getAccountLocations')->name('admin.accounts.locations');
            Route::get('/{id}/unban', 'UiAdminAccountsController@getAccountUnban')->name('admin.accounts.unban');
            Route::post('/{id}/ban', 'UiAdminAccountsController@postAccountBan')->name('admin.accounts.ban');
            Route::get('/{id}', 'UiAdminAccountsController@getAccount')->name('admin.accounts.account');
        });

        Route::get('/transactions', 'UiAdminAccountsController@transactions')->name('admin.transactions');
    });

    Route::get('/accounts/switch-account/{id}', 'UiAccountController@switchShadowAccount')->name('admin.accounts.shadow.switch');

});
