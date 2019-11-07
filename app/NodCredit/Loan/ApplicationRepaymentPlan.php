<?php
namespace App\NodCredit\Loan;

class ApplicationRepaymentPlan
{

    public static function generateHtmlTable(Application $application): string
    {
        $table = '
            <table border="1" cellpadding="0" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <td style="padding: 5px; font-weight: bold;">Amount</td>
                    <td style="padding: 5px; font-weight: bold;">Due Date</td>
                    <td style="padding: 5px; font-weight: bold;">Month</td>
                </tr>
            </thead>
            <tbody>';

        /** @var Payment $payment */
        foreach ($application->getPayments()->all() as $payment) {
            $table .= "
                <tr>
                    <td style='padding: 5px;'>NGN" . number_format($payment->getAmount(),2) . "</td>
                    <td style='padding: 5px;'>" . $payment->getDueAt() . "</td>
                    <td style='padding: 5px;'>" . $payment->getPaymentMonth() . "</td>
                </tr>
            ";
        }

        $table .= '</tbody></table>';

        return $table;
    }

}