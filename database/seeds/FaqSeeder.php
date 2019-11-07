<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\FaqItem::truncate();

        $investCategory = [
            [
                'title' => 'I want to Invest on Nodcredit platform, what does it mean, and do I get my funds on demand?',
                'text' => 'You can earn good returns on your investments with us. Nodcredit makes your money work for you while you earn good returns. Pre-liquidation, either fully or part liquidation is on demand, instant and completely automated. See our terms of service for charges and other conditions.',
            ],
            [
                'title' => 'How long does it take to get my money after liquidation or at maturity?',
                'text' => 'We credit our investors instantly when their investments mature or if the investment is liquidated before maturity. However, for large investments it may take up to 3 – 5 working days for funds to reach the investors bank account.',
            ],
            [
                'title' => 'What is the minimum tenor I can invest my funds?',
                'text' => 'You can place funds with Nodcredit for 92 days, 184 days and 365 days. The minimum tenor is 92 days and the maximum tenor is 365 days.',
            ],
            [
                'title' => 'How much Interest do I earn on my Investment?',
                'text' => 'Our interest rates vary from time to time, you can find applicable rates for chosen tenor when you register and sign in to a Nodcredit account.',
            ],
            [
                'title' => 'Are interests payable monthly or at maturity?',
                'text' => 'Interests are calculated on a per annum basis, and payable at maturity or when the investment is liquidated.',
            ],
            [
                'title' => 'Are there penalties for early liquidation?',
                'text' => 'We charge 40% on the interest earned if you liquidate your investment before maturity. Your principal sum is not affected in any case.',
            ],
            [
                'title' => 'I like what you guys are doing at Nodcredit, I want to invest a high amount, can I negotiate?',
                'text' => 'Yes, you can negotiate investments above certain amount; send an email to us at invest@nodcredit.com',
            ],
            [
                'title' => 'How am I sure I will get my money back, is this a fixed term deposit?',
                'text' => 'We disburse thousands of loans to Nigerians weekly and we are growing daily. Investments on Nodcredit helps us reach more people and drive consumer finance in Nigeria. All Investments are on Nodcredit as an entity. Investment on Nodcredit only shares similar features as a fixed term deposit.',
            ],
            [
                'title' => 'I want to be part of Nodcredit, Can I convert my Investment to Equity?',
                'text' => 'Yes, you can. Our goal is to drive consumer finance and boost economic activities. If you want to do the same, send us an email at invest@nodcredit.com',
            ],
        ];

        $loanCategory = [
            [
                'title' => 'What is a personal loan?',
                'text' => 'A personal loan is money you borrow for any kind of personal use such as paying off previous debt, credit card debt, investing in home improvements, taking a special vacation, or paying for an engagement ring or wedding expenses. Taking a personal loan can be a smart way to consolidate high-interest rate balances under one monthly rate. To repay the loan, you make monthly payments of principal plus interest. A personal loan gives you the flexibility to make big purchases, then pay it off at a pace that makes sense for you.',
            ],
            [
                'title' => 'What can I use a Nodcredit Personal Loan for?',
                'text' => 'Nodcredit Personal Loans are solely for personal, family, or household purposes and are not permitted to be used for real estate, business purposes, investments, purchases of securities, post-secondary education and short-term bridge financing.',
            ],
            [
                'title' => 'Am I eligible for a Nodcredit Personal Loan?',
                'text' => 'To be eligible for a Nodcredit loan, you must be a Nigerian citizen, 18 years or older, employed or have verifiable means of income. Loan eligibility also depends on a number of additional factors, such as a responsible financial history, your monthly income vs. expenses, and professional experience. Please review our Eligibility Criteria for further details.',
            ],
            [
                'title' => 'What is the maximum tenor for a Nodcredit Personal Loan?',
                'text' => 'Currently 1 month or 30 days whichever comes earlier.',
            ],
            [
                'title' => 'What Interest rate does Nodcredit charge for its Personal Loan?',
                'text' => 'We charge a fixed rate of 15% flat a month.',
            ],
            [
                'title' => 'Am I a good candidate for a Nodcredit Personal Loan?',
                'text' => 'Nodcredit aims to revolutionize financial services, ultimately improving the system for everyone. Today, we’re able to offer significant loan flexibility to individuals who are employed and/or has a sufficient income from other sources with a responsible financial history and have a strong monthly cash flow.',
            ],
            [
                'title' => 'Is the Nodcredit Personal Loan secured or unsecured?',
                'text' => 'The Nodcredit Personal Loan is an unsecured loan. This means that you do not need to provide collateral for the loan or a Guarantor.',
            ],
            [
                'title' => 'What is the minimum and maximum I can borrow?',
                'text' => 'With the Nodcredit Personal Loan, the minimum amount you can borrow is NGN10,000 and the maximum is N50,000.',
            ],
            [
                'title' => 'Are there any origination or prepayment fees?',
                'text' => 'No. We want to make things simple for our customers, so we have no origination fees, closing costs, or prepayment penalties. Note that any additional payments will be paid first toward the accrued interest, then toward the principal balance on your account. You’re only responsible for the interest on the principal balance on the time that you have it.',
            ],
            [
                'title' => 'How is a Nodcredit Personal Loan different from credit card debt?',
                'text' => 'Nodcredit Personal Loans have a fixed repayment term. Credit cards often have high variable rates and no set repayment term.',
            ],
            [
                'title' => 'How long does it take to receive the funds?',
                'text' => 'Once your application is complete and verified, if you are approved for a loan, your funds should generally be available within 24hours.',
            ],
            [
                'title' => 'What is Pause option',
                'text' => 'We allow customers to extend their loan by an additional 30days. This option is called PAUSE on our website and can be found in the Loan repayment tab. Please note that you will be charged 15% of the loan amount due whenever you use the PAUSE button.',
            ],
            [
                'title' => 'What if I am laid off and can’t pay my monthly installments?',
                'text' => 'We encourage you to contact us as early as possible if you become unemployed.',
            ],
            [
                'title' => 'What if I’m late in making a payment?',
                'text' => 'Timely payment of your Nodcredit loans helps ensure we can continue to deliver great products and services to other Nodcredit customers. We may charge late fees or penal rate for delays over 2days.',
            ],
            [
                'title' => 'How can I make my loan payments?',
                'text' => 'The easiest way to pay is to log in to your Nodcredit.com account and make payments electronically via your registered debit or credit card linked to your primary bank account. We use your card details to set up an auto debit to deduct repayments at agreed and specified periods. A confirmation charge may be incurred by the customer during the card set-up. You can also make direct credit to Nodens Nigeria Limited bank account.',
            ],
            [
                'title' => 'What if I hotlist my card before payment date?',
                'text' => 'We encourage you to request for another card from your financial institution and re-profile the new card to your Nodcredit account and make payments manually. You may also make direct bank transfers to Nodens Nigeria Limited Bank account.',
            ],
            [
                'title' => 'Can I change my monthly payment date?',
                'text' => 'Yes, you may change your payment date to any date between the 1st and 25th of the month. You can only change your payment date once per year. Your loan must be current, and at least the first payment must be processed for it to take effect. However, as a new company. This service is not currently available. Our customers will be notified once this feature is added to our platform.',
            ],
            [
                'title' => 'Will making a large payment or additional payments change my monthly due amount?',
                'text' => 'Making a large payment or additional payment on a Personal Loan will not automatically reduce your monthly payment. If you make a large or additional payment, the first part of the payment goes toward any interest accrued. The remainder goes toward the principal balance. Your monthly payment amount will stay the same, but you will be on track to pay your loan off early. After making a substantial payment you are welcome to reach out at support@nodcredit.com and request to reamortize your loan, which may bring your payment down.',
            ],
            [
                'title' => 'Can I refinance my current Nodcredit Personal Loanfrom another Financial Institution?',
                'text' => 'Yes.',
            ],
            [
                'title' => 'How are funds disbursed?',
                'text' => 'Funds are sent directly to your personal bank account under your name',
            ],
            [
                'title' => 'How can I amortize my personal loan?',
                'text' => 'The re-amortization process is simple and just needs your request in writing by sending an email from you via the email address you have with us on file to support@nodcredit.com stating that you would like to re-amortize. We will then be able to start the process of re-amortization and will reach out to you with any questions or updates.',
            ],
            [
                'title' => 'I keep getting token expired error',
                'text' => 'This means your page has timed out. All you have to do is sign in again',
            ],
            [
                'title' => 'Why can’t I delete my account?',
                'text' => 'All requests to delete account must be sent to support@nodcredit.com. We have to make sure customers with running loans do not delete their accounts and abscond',
            ],
            [
                'title' => 'Why do I have to upload my bank statement and Identity Card',
                'text' => 'We use the bank statement to analyse transaction history and to determine the customer’s capacity to repay his/her loan. The identity card uploaded helps increase the applicants chances of accessing a loan on NODCREDIT',
            ],
            [
                'title' => 'I applied for N50,000 and got a lower amount',
                'text' => 'All our applicants are scored to determine eligibility. We encourage our customers to provide the required documents so we can meet individual needs',
            ],
            [
                'title' => 'I am a regular customer, why do I need to keep uploading documents',
                'text' => 'We are developing daily and we are working on simpler solutions to help you meet your financial needs easier and faster.',
            ],
            [
                'title' => 'I want to Invest with Nodcredit, do I get my funds on demand?',
                'text' => 'You can earn good returns on your investments with us. Pre-liquidation and part liquidation is on demand and automated. See our terms of service for charges and other conditions',
            ],
        ];

        foreach ($investCategory as $index => $item) {
            $item['category'] = 'invest';
            $item['is_active'] = true;
            $item['sort'] = $index;

            \App\Models\FaqItem::create($item);
        }

        foreach ($loanCategory as $index => $item) {
            $item['category'] = 'loan';
            $item['is_active'] = true;
            $item['sort'] = $index;

            \App\Models\FaqItem::create($item);
        }

    }
}
