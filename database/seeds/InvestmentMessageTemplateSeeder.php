<?php

use Illuminate\Database\Seeder;

class InvestmentMessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-started');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-started',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Congratulations! Your Investment Has Started.',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>Your investment has started.</p>
                    <p><b>Investment Details</b></p>
                    <p><b>Amount</b>: #INVESTMENT_AMOUNT#</p>
                    <p><b>Tenor</b>: #INVESTMENT_PLAN_NAME#</p>
                    <p><b>Percentage</b>: #INVESTMENT_PLAN_PERCENTAGE#%</p>
                    <p><b>PS: You can liquidate your investment anytime.</b></p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-liquidation-request');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-liquidation-request',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'You Are Liquidating Your Investment',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>We received your liquidate request with this note.</p>
                    <p><strong>#LIQUIDATION_REASON#</strong></p>
                    <p><strong>PS: Your liquidation is in process and your account will be credited in 24 hours.</strong></p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-partial-liquidation-paid');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-partial-liquidation-paid',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment Payment from NodCredit',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>We have made a transfer of #LIQUIDATION_AMOUNT# to your official bank account for the investment you just liquidated with us.</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-full-liquidation-paid');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-full-liquidation-paid',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment Payment from NodCredit',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>We have made a transfer of #LIQUIDATION_AMOUNT# to your official bank account for the investment you just liquidated with us.</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-added');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-added',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'New Investment With NodCredit',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>You have successfully added an investment with NodCredit.</p>
                    <p>Your investment is been reviewed and will start shortly.</p>
                    <p><b>Investment Details</b></p>
                    <p><b>Amount</b>: #INVESTMENT_AMOUNT#</p>
                    <p><b>Tenor</b>: #INVESTMENT_PLAN_NAME#</p>
                    <p><b>Percentage</b>: #INVESTMENT_PLAN_PERCENTAGE#%</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('admin-investment-added');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'admin-investment-added',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'New Investment On NodCredit',
                'message' => "
                    <p>Hello admin,</p>
                    <p>Customer has successfully added an investment on NodCredit.</p>
                    <p><b>Investment Details</b></p>
                    <p><b>Customer name</b>: #USER_NAME#</p>
                    <p><b>Customer email</b>: #USER_EMAIL#</p>
                    <p><b>Amount</b>: #INVESTMENT_AMOUNT#</p>
                    <p><b>Tenor</b>: #INVESTMENT_PLAN_NAME#</p>
                    <p><b>Percentage</b>: #INVESTMENT_PLAN_PERCENTAGE#%</p>
                    <p><a href=\"#INVESTMENT_URL#\" style=\"display: inline-block; background-color: #FF9000; border-radius: 2px; box-shadow: inset 0 10px 40px 0 rgba(255, 144, 0, 0.67); padding: 16px 25px; text-decoration: none; color: #FFFFFF; font-size: 14px; font-weight: 500; line-height: 17px; text-align: center;\">View Investment</a></p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('admin-investment-liquidation-request');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'admin-investment-liquidation-request',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment Liquidation Request',
                'message' => "
                    <p>Hello admin,</p>
                    <p>We received liquidate request with these details:</p>
                    <p><b>Customer name</b>: #USER_NAME#</p>
                    <p><b>Customer email</b>: #USER_EMAIL#</p>
                    <p><b>Liquidation amount:</b> #LIQUIDATION_AMOUNT#</p>
                    <p><b>Reason:</b> #LIQUIDATION_REASON#</p>
                    <p><b>#LIQUIDATION_PAYOUT#</b></p>
                    <p><a href=\"#INVESTMENT_URL#\" style=\"display: inline-block; background-color: #FF9000; border-radius: 2px; box-shadow: inset 0 10px 40px 0 rgba(255, 144, 0, 0.67); padding: 16px 25px; text-decoration: none; color: #FFFFFF; font-size: 14px; font-weight: 500; line-height: 17px; text-align: center;\">View Investment</a></p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-completed');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-completed',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment is completed',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>Your investment has completed.</p>
                    <p><b>Investment Details</b></p>
                    <p><b>Amount</b>: #INVESTMENT_AMOUNT#</p>
                    <p><b>Tenor</b>: #INVESTMENT_PLAN_NAME#</p>
                    <p><b>Percentage</b>: #INVESTMENT_PLAN_PERCENTAGE#%</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('admin-investment-completed');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'admin-investment-completed',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment is completed',
                'message' => "
                    <p>Hello admin,</p>
                    <p>Investment has completed.</p>
                    <p><b>Investment Details</b></p>                    
                    <p><b>Customer name</b>: #USER_NAME#</p>
                    <p><b>Customer email</b>: #USER_EMAIL#</p>
                    <p><b>Amount</b>: #INVESTMENT_AMOUNT#</p>
                    <p><b>Tenor</b>: #INVESTMENT_PLAN_NAME#</p>
                    <p><b>Percentage</b>: #INVESTMENT_PLAN_PERCENTAGE#%</p>
                    <p><a href=\"#INVESTMENT_URL#\" style=\"display: inline-block; background-color: #FF9000; border-radius: 2px; box-shadow: inset 0 10px 40px 0 rgba(255, 144, 0, 0.67); padding: 16px 25px; text-decoration: none; color: #FFFFFF; font-size: 14px; font-weight: 500; line-height: 17px; text-align: center;\">View Investment</a></p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-paid');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-paid',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Completed Investment Payment from NodCredit',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>Your investment is completed and we have made a transfer of #INVESTMENT_PAYOUT_AMOUNT# to your official bank account.</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('admin-investment-paid');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'admin-investment-paid',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Completed Investment Payment',
                'message' => "
                    <p>Hello admin,</p>
                    <p>Investment is completed and we have made a transfer of the principal to the customer.</p>
                    <p><b>Customer name</b>: #USER_NAME#</p>
                    <p><b>Customer email</b>: #USER_EMAIL#</p>
                    <p><b>Paid out</b>: #INVESTMENT_PAYOUT_AMOUNT#</p>
                    <p><a href=\"#INVESTMENT_URL#\" style=\"display: inline-block; background-color: #FF9000; border-radius: 2px; box-shadow: inset 0 10px 40px 0 rgba(255, 144, 0, 0.67); padding: 16px 25px; text-decoration: none; color: #FFFFFF; font-size: 14px; font-weight: 500; line-height: 17px; text-align: center;\">View Investment</a></p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('admin-investment-profit-payment-paid');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'admin-investment-profit-payment-paid',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment Interest Payment',
                'message' => "
                    <p>Hello admin,</p>
                    <p>We have made a transfer of interest to the customer.</p>
                    <p><b>Customer name</b>: #USER_NAME#</p>
                    <p><b>Customer email</b>: #USER_EMAIL#</p>
                    <p><b>Interest amount</b>: #PROFIT_PAYMENT_AMOUNT#</p>
                    <p><a href=\"#INVESTMENT_URL#\" style=\"display: inline-block; background-color: #FF9000; border-radius: 2px; box-shadow: inset 0 10px 40px 0 rgba(255, 144, 0, 0.67); padding: 16px 25px; text-decoration: none; color: #FFFFFF; font-size: 14px; font-weight: 500; line-height: 17px; text-align: center;\">View Investment</a></p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-profit-payment-paid');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-profit-payment-paid',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment Interest Payment',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>We have made a transfer of interest (#PROFIT_PAYMENT_AMOUNT#) to your official bank account.</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('investment-profit-payment-reminder');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'investment-profit-payment-reminder',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment Interest Payment Reminder',
                'message' => "
                    <p>Hello #USER_NAME#,</p>
                    <p>Your interest payment is due in 24 hours.</p>
                    <p>You will receive payment of #PROFIT_PAYMENT_AMOUNT# in 24 hours.</p> 
                    <p>Payment will be made to:</p>
                    <p><b>Bank:</b> #USER_BANK_NAME#</p>
                    <p><b>Account number:</b> #USER_BANK_ACCOUNT_NUMBER#</p>
                    <p><b>Account name:</b> #USER_BANK_ACCOUNT_NAME#</p>
                    <p>If you prefer to change it please contact Yomi @ 08094278889</p>
                    <p>Thanks for your business!</p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

        try {
            $exists = \App\NodCredit\Message\Template::findByKey('admin-investment-profit-payment-reminder');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $template = [
                'key' => 'admin-investment-profit-payment-reminder',
                'channel' => \App\NodCredit\Message\Template::CHANNEL_EMAIL,
                'title' => 'Investment Interest Payment Reminder',
                'message' => "
                    <p>Hello admin,</p> 
                    <p>Customer with investment #INVESTMENT_AMOUNT# is due to interest payment in 24 hours.</p>
                    <p><b>Customer name</b>: #USER_NAME#</p>
                    <p><b>Customer email</b>: #USER_EMAIL#</p>
                    <p><b>Interest amount</b>: #PROFIT_PAYMENT_AMOUNT#</p>
                    <p>When a scheduled date comes, you can manually pay interest on the Investment page.</p>
                    <p><a href=\"#INVESTMENT_URL#\" style=\"display: inline-block; background-color: #FF9000; border-radius: 2px; box-shadow: inset 0 10px 40px 0 rgba(255, 144, 0, 0.67); padding: 16px 25px; text-decoration: none; color: #FFFFFF; font-size: 14px; font-weight: 500; line-height: 17px; text-align: center;\">View Investment</a></p>
                ",
            ];

            \App\NodCredit\Message\TemplateFactory::create($template);
        }

    }
}
