<?php

use Illuminate\Database\Seeder;

class AddMessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-due-payment-charge-for-pause');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-due-payment-charge-for-pause',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Loan payment is due. Loan payment pause charging',
                'message' => 'We have successful charged for your loan payment pause which started on #PAYMENT_DUE_AT_OLD#. Remember your loan payment is due on #PAYMENT_DUE_AT#',
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-due-payment-charge-failed');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-due-payment-charge-failed',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_BOTH,
                'title' => 'Payment Error',
                'message' => 'Error processing loan repayment. Our system will attempt this action again in few hours.',
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-due-payment-charge-partially-success');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-due-payment-charge-partially-success',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Please complete your loan repayment',
                'message' => "We have received your payment of #CHARGED_AMOUNT#. This payment accounts for half of your due loan made for the 1st month of your loan re-payment. Outstanding loan amount -  #PAYMENT_AMOUNT#. \n\rFor more information log into your account.\n\r\n\r- NodCredit Team",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-rejected');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-rejected',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Loan Application rejected',
                'message' => "We regret to inform you that your loan application has been rejected and we can not accede to your request at this time.",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-processing');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-processing',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Your Loan is being processed',
                'message' => "<p>Hello #USER_NAME#,</p><p>Your Loan is being processed.</p><p>For more information log into your account.</p>",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-confirm-new-amount');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-confirm-new-amount',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Loan Application new amount confirmation',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>From our review of your Bank Statement, you're only able to apply for a maximum of <b>#LOAN_AMOUNT_ALLOWED#</b>.</p>
                    <p>Should we continue with your Loan approval?</p>
                    <div style=\"margin: 20px 0;\">
                        <a href=\"#LOAN_AMOUNT_CONFIRM_URL#\" style=\"padding: 10px 20px; background-color: #2fca74; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            Yes
                        </a>
                        <a href=\"#LOAN_AMOUNT_REJECT_URL#\" style=\"padding: 10px 20px; background-color: #e31000; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            No
                        </a>
                    </div>",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-invalid-statement-period');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-invalid-statement-period',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Loan Application: Bank Statement period is not valid',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>Your uploaded Bank Statement period is not valid. #REASON#</p>
                    <p>Please, upload valid bank statement.</p>
                    <div style=\"margin: 20px 0; \">
                        <a href=\"#LOAN_URL#\" style=\"padding: 10px 20px; background-color: #0668e3; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            Go to Loan Application
                        </a>
                    </div>",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-confirm-last-approved-amount');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-confirm-last-approved-amount',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Loan Application new amount confirmation',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>From our review of your Bank Statement, we can only approve your previous completed loan amount - <b>#LOAN_AMOUNT_ALLOWED#</b>.</p>
                    <p>Should we continue with your Loan approval?</p>
                    <div style=\"margin: 20px 0;\">
                        <a href=\"#LOAN_AMOUNT_CONFIRM_URL#\" style=\"padding: 10px 20px; background-color: #2fca74; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            Yes
                        </a>
                        <a href=\"#LOAN_AMOUNT_REJECT_URL#\" style=\"padding: 10px 20px; background-color: #e31000; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            No
                        </a>
                    </div>",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-reconfirmation');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-reconfirmation',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Loan Application re-confirmation',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>You applied for a loan #LOAN_AGE_IN_DAYS# days ago,</p>
                    <p>To confirm you're still interested in the loan, click YES, and we will go ahead to process your documents.</p>
                    <div style=\"margin: 20px 0;\">
                        <a href=\"#LOAN_HANDLING_CONFIRM_URL#\" style=\"padding: 10px 20px; background-color: #2fca74; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            Yes
                        </a>
                        <a href=\"#LOAN_HANDLING_REJECT_URL#\" style=\"padding: 10px 20px; background-color: #e31000; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            No
                        </a>
                    </div>",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-money-transferred');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-money-transferred',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Account credited',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>Congratulations! We just made a transfer of #LOAN_AMOUNT_APPROVED# to your account.</p>
                    <p>Find below your re-payment plan</p>
                    #LOAN_REPAYMENT_PLAN#
                    <p>Log into your account for more details.</p>",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-manually-confirm-new-amount');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-manually-confirm-new-amount',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Loan Application new amount confirmation',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>From our review of your Bank Statement, you're only able to apply for a maximum of <b>#LOAN_AMOUNT_ALLOWED#</b>.</p>
                    <p>Should we continue with your Loan approval?</p>
                    <div style=\"margin: 20px 0;\">
                        <a href=\"#LOAN_AMOUNT_CONFIRM_URL#\" style=\"padding: 10px 20px; background-color: #2fca74; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            Yes
                        </a>
                        <a href=\"#LOAN_AMOUNT_REJECT_URL#\" style=\"padding: 10px 20px; background-color: #e31000; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 20px 0px; margin-right: 20px; text-decoration: none; border-radius: 5px\">
                            No
                        </a>
                    </div>",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-required-document-not-uploaded');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-required-document-not-uploaded',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => '[Action Required] Your Loan Is Pending',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>You recently applied for a loan but yet to provide us with a valid work ID and your bank statement (previous 1 to 3 months) as part of the loan assessment.</p>
                    <p>If you have provided the following documents, then kindly ignore this mail, else your loan application will be rejected in 24 hours.</p>
                    ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-application-status-notification');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-application-status-notification',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Update on Your Loan',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>New update on your loan application. The status has changed to <strong>#LOAN_STATUS#</strong></p>
                    <p>For more information log into your account.</p>
                    ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('user-loan-range-reached');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'user-loan-range-reached',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Access to New Loans',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>You score is now #USER_SCORES#, you can access upto #LOAN_RANGE_MAX_AMOUNT# at #USER_INTEREST_RATE#% now and can extend payment over #LOAN_RANGE_MAX_MONTH# months</p>
                    ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-repayment-reminder-15-days');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-repayment-reminder-15-days',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Do NOT wait till your loan is DUE',
                'message' => "
                    <p>Hey #USER_NAME#,</p>
                    <p>You know you don't have to wait till your loan is closely due before you starting attending to it.</p>
                    <p>On NodCredit you have the option to offset portions of your loans anytime. Giving you full control on your repayment schedule.</p>
                    <p>Go to your loan re-payment page, enter the amount you want to pay in your loan amount field, select your preferred card and click on the 'Pay Now' Button. Voila!</p>
                    <p><img src='https://ogamarketer.com/storage/uploads/email-templates/fb17f700-b2b4-11e9-89ce-679df20512e5/Ci1EK4oixYD1SKLB0icCS994Qcs2TCDbH1fHF9vv.gif' alt=''></p>
                    <p>No need to wait till your loan is due before settling your outstanding balance, you can start with as low as NGN 1000 whenever you have it.</p>
                    <p>Login to your NodCredit account, go to your loan repayment page.</p>
                    <p>Good Luck!</p>
                    <p>NodCredit</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('loan-due-payment-daily-penalty-reminder');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'loan-due-payment-daily-penalty-reminder',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => '#TODAY# Reminder (#PAYMENT_DUE_DAYS# days Past Due)',
                'message' => "
<p>Hello #USER_NAME#,</p>
<p>You are presently in default. Your outstanding today is #PAYMENT_AMOUNT#.</p>
<p>Default will continue to attract 1% interest daily. See growth graph below for next 30 days increase.</p>
<p><img src='#AMOUNT_GROWTH_IMAGE_URL#' width='500' height='300' style='display:block' title='Growth graph' alt='growth graph'></p>
<p>You can also negotiate your settlement now if you are having reasonable difficulties paying back your loan.</p>
<table>
    <tbody>
        <tr>
            <td style='width: 250px; vertical-align: top; padding-right: 20px;'>
                <a href='#LOAN_REPAYMENT_URL#'  style='padding: 10px 20px; background-color: #2fca74; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 10px 0px; text-decoration: none; border-radius: 5px'>Pay Now</a>
                <p style='font-size: 90%;'>Offset what you owe now by upto #LOAN_DUE_PAYMENTS_PENALTY_PAUSE_THRESHOLD#% <br> and the 1% daily interest increase will stop for #LOAN_DUE_PAYMENTS_PENALTY_PAUSE_DAYS# days</p>
            </td> 
            <td style='width: 250px; vertical-align: top;'>
                <a href='tel:+2348102391677' style='padding: 10px 20px; background-color: #2fca74; color: #fff; font-weight: bolder; font-size: 16px; display: inline-block; margin: 10px 0px; text-decoration: none; border-radius: 5px'>Call Now</a>
                <p style='font-size: 90%;'>Call Mike now to negotiate your settlement.</p>            
            </td>
        </tr>
    </tbody>
</table>
<p>The following actions are now necessary and will be implemented at our own discretion when we deem you unresponsive:</p>
<ul>
    <li>Report delinquent loans to credit bureaus</li>
    <li>Send Final Demand notice letter to Residential and Work Address</li>
    <li>Publish LinkedIn and social media handles</li>
    <li>Contact closest family and friends via email and phone number</li>
    <li>Send messages to all the phone numbers found on your bank statement</li>
    <li>Court Order to restrict all defaulters Bank account and apprehend on sight.</li>
</ul>
<p>
    Regards <br>
    NodCredit Recovery
</p>
",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }


        try {
            $exists = \App\NodCredit\Message\Template::findByKey('welcome-customer');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'welcome-customer',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Welcome to NodCredit',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>You have successfully sign up to Nodcredit.</p>
                    <p>To apply for your first loan, go to profile page to add your details.</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('welcome-investor-registered-by-himself');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'welcome-investor-registered-by-himself',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Welcome to NodCredit',
                'message' => "
                    <p>Hi,</p>
                    <p>I'm Abayomi, the CEO at Nodcredit, and I'd like to personally welcome you. Nodcredit is changing lending in Africa and we want you to be part of this amazing financial journey.</p>
                    <p>Now if there's one thing CEOs are good at; it's getting right to the point. We have set up a Nodcredit account for you, this will get you immediate access to:</p>
                    <ul>
                        <li>View Existing Investments</li>
                        <li>Liquidate Investments</li>
                        <li>Add to Investments</li>
                    </ul>
                    <p>We want all our debt investors to have control of their funds.</p>  
                    <p>You can add to your investments at any time or part-liquidate with appropriate notice.</p>
                    <p>You can complete your profile on our page to enable us to provide customized solutions to you.</p>
                    <p>Sincerely</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('welcome-investor-registered-by-admin');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'welcome-investor-registered-by-admin',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Welcome to NodCredit',
                'message' => "
                    <p>Hi #USER_NAME#,</p>
                    <p>I'm Abayomi, the CEO at Nodcredit, and I'd like to personally welcome you. Nodcredit is changing lending in Africa and we want you to be part of this amazing financial journey.</p>
                    <p>Now if there's one thing CEOs are good at; it's getting right to the point. We have set up a Nodcredit account for you, this will get you immediate access to:</p>
                    <ul>
                        <li>View Existing Investments</li>
                        <li>Liquidate Investments</li>
                        <li>Add to Investments</li>
                    </ul>
                    <p>We want all our debt investors to have control of their funds.</p>  
                    <p>Please find below your logon details to access your investments. You can add to your investments at any time or part-liquidate with appropriate notice.</p>
                    <p>
                        Login: #USER_EMAIL# <br>    
                        Password: #PASSWORD#
                    </p>    
                    <p>You can complete your profile on our page to enable us to provide customized solutions to you.</p>
                    <p>Sincerely</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

    }
}
