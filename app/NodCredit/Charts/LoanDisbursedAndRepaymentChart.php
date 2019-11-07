<?php
namespace App\NodCredit\Charts;

use App\NodCredit\Loan\Application;
use App\TransactionLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LoanDisbursedAndRepaymentChart
{

    public function build(Carbon $inStart, Carbon $inEnd = null): array
    {

        $categories = $this->buildChartCategories($inStart, $inEnd);

        $records = $this->findRecords($inStart, $inEnd);

        $chart = [
            'chart' => ['type' => 'column'],
            'title' => ['text' => ' '],
            'xAxis' => [
                'categories' => $categories,
            ],
            'yAxis' => [
                'min' => 0,
                'title' => ['text' => 'Amount, NGN']
            ],
            'plotOptions' => [
                'column' => [
                    'grouping' => false,
                    'shadow' => false,
                    'borderWidth' => 0
                ]
            ],
            'credits' => ['enabled' => false],
            'series' => [
                [
                    'name' => 'Out',
                    'data' =>  $this->buildChartSerieOutData($records['out'], $categories),
                    'pointPadding' => 0.3,
                    'color' => 'rgba(255,0,0,0.5)'
                ],
                [
                    'name' => 'In',
                    'data' =>  $this->buildChartSerieData($records['in'], $categories),
                    'pointPadding' => 0.35,
                    'color' => 'rgba(0,255,0,0.5)'
                ],
            ]
        ];

        return $chart;
    }

    private function buildChartCategories(Carbon $inStart, Carbon $inEnd = null)
    {
        $categories = [];

        if (! $inEnd) {
            $categories = [$inStart->toDateString()];
        }
        else {
            for ($date = clone $inStart; $date->lte($inEnd); $date->addDay()) {
                $categories[] = $date->toDateString();
            }
        }

        return $categories;
    }

    protected function buildChartSerieData(Collection $records, array $categories): array
    {
        $data = array_flip($categories);

        $data = array_map(function() {
            return 0;
        }, $data);

        foreach ($records as $record) {
            $data[$record->date] = [$record->date, floatval($record->total)];
        }

        return array_values($data);
    }

    protected function buildChartSerieOutData(Collection $records, array $categories): array
    {
        $data = array_flip($categories);

        $data = array_map(function() {
            return 0;
        }, $data);

        foreach ($records as $record) {
            $categoryDateKey = Carbon::createFromFormat('Y-m-d', $record->date)->addMonth();
            $data[$categoryDateKey->toDateString()] = [$record->date, floatval($record->total)];
        }

        return array_values($data);
    }

    private function findRecords(Carbon $inStart, Carbon $inEnd = null)
    {
        $inStart->startOfDay();

        if (! $inEnd) {
            $inEnd = clone $inStart;
        }

        $inEnd->endOfDay();

        $outStart = clone $inStart;
        $outStart->subMonth();
        $outEnd = clone $inEnd;
        $outEnd->subMonth();

        $outBuilder = DB::table('loan_applications')
            ->select(
                DB::raw('SUM(`amount_approved`) as `total`'),
                DB::raw('DATE_FORMAT(`paid_out`, "%Y-%m-%d") as `date`')
            )
            ->whereIn('status', [Application::STATUS_APPROVED, Application::STATUS_COMPLETED])
            ->whereBetween('paid_out', [$outStart, $outEnd])
            ->groupBy('date')
            ->orderBy('date')
        ;

        $inBuilder = DB::table('transaction_logs')
            ->select(
                DB::raw('SUM(`amount`) as `total`'),
                DB::raw('DATE_FORMAT(`created_at`, "%Y-%m-%d") as `date`')
            )
            ->where('status', TransactionLog::STATUS_SUCCESSFUL)
            ->where('trans_type', 'debit')
            ->where('model', 'LoanPayment')
            ->whereBetween('created_at', [$inStart, $inEnd])
            ->groupBy('date')
            ->orderBy('date')
        ;

        return [
            'in' => $inBuilder->get(),
            'out' => $outBuilder->get(),
        ];
    }

}