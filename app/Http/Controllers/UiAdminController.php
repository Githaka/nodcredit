<?php

namespace App\Http\Controllers;

use App\LoanApplication;
use App\LoanPayment;
use App\NodCredit\Charts\AgeChart;
use App\NodCredit\Charts\BankChart;
use App\NodCredit\Charts\GenderChart;
use App\NodCredit\Charts\LoanAmountRequestedChart;
use App\NodCredit\Charts\LoanDisbursedAndRepaymentChart;
use App\NodCredit\Charts\LoanTypeChart;
use App\NodCredit\Charts\ResponsiveDefaultersChart;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Payment;
use App\TransactionLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class UiAdminController extends AdminController
{


    public function postDisbursedAndRepaymentChart(Request $request)
    {
        $filterDateType = $request->get('filter_date_type');
        $filterCustomDate = $request->get('filter_custom_date');

        if ($filterDateType === 'custom') {
            $inStart = Carbon::createFromFormat('Y-m-d', $filterCustomDate)->startOfDay();
            $inEnd = Carbon::createFromFormat('Y-m-d', $filterCustomDate)->endOfDay();
        }
        else if ($filterDateType === 'last-30-days') {
            $inStart = now()->subDays(29)->startOfDay();
            $inEnd = now()->endOfDay();
        }
        else if ($filterDateType === 'last-14-days') {
            $inStart = now()->subDays(13)->startOfDay();
            $inEnd = now()->endOfDay();
        }
        else if ($filterDateType === 'today') {
            $inStart = now()->startOfDay();
            $inEnd = now()->endOfDay();
        }
        else {
            $inStart = now()->subDays(6)->startOfDay();
            $inEnd = now()->endOfDay();
        }

        return response()->json([
            'chart' => (new LoanDisbursedAndRepaymentChart())->build($inStart, $inEnd)
        ]);
    }

    public function getCustomersCharts()
    {

        $defaultersBuilder = DB::table('loan_payments')
            ->select('loan_applications.user_id')
            ->join('loan_applications', function($join) {
                $join->on('loan_applications.id', '=', 'loan_payments.loan_application_id');
                $join->where('loan_applications.status', Application::STATUS_APPROVED);
                $join->whereNotNull('loan_applications.paid_out');
            })
            ->where('loan_payments.status', Payment::STATUS_SCHEDULED)
            ->where('loan_payments.due_at', '<', now())
        ;

        $defaultersIds = $defaultersBuilder->get()->pluck('user_id')->unique()->toArray();

        $responsiveBuilder = DB::table('loan_applications')
            ->select('user_id', DB::raw('COUNT(`user_id`) as `count`'))
            ->groupBy('user_id')
            ->having('count', '>=', 1)
            ->orderBy('count', 'DESC')
            ->where('status', Application::STATUS_COMPLETED)
        ;

        $responsiveIds = $responsiveBuilder->get()->pluck('user_id')->toArray();
        $responsiveIds = array_diff($responsiveIds, $defaultersIds);

        $responsive = User::whereIn('id', $responsiveIds)->get();
        $defaulters = User::whereIn('id', $defaultersIds)->get();


        $series = [
            ['name' => 'Responsive', 'collection' => $responsive],
            ['name' => 'Defaulters', 'collection' => $defaulters],
        ];

        return response()->json([
            'sex_chart' => GenderChart::generate($series),
            'age_chart' => AgeChart::generate($series),
            'loan_type_chart' => LoanTypeChart::generate($series),
            'bank_chart' => BankChart::generate($series),
            'loan_amount_requested_chart' => LoanAmountRequestedChart::generate($series),
            'responsive_defaulters_chart' => ResponsiveDefaultersChart::generate([['name' => 'Responsive Defaulters', 'collection' => $defaulters]]),
            'counters' => [
                'responsive' => $responsive->count(),
                'defaulters' => $defaulters->count(),
            ]
        ]);
    }

}
