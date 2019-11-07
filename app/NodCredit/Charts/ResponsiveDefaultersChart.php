<?php
namespace App\NodCredit\Charts;

use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Payment;
use App\User;
use Illuminate\Support\Collection;

class ResponsiveDefaultersChart extends Chart
{
    /**
     * @var array
     */
    protected $chartCategories = [
        'upto 5%',
        'upto 10%',
        'upto 25%',
        'upto 50%',
        'upto 100%',
    ];

    public function build(): array
    {
        $chartSeries = [];

        foreach ($this->series as $serie) {
            $chartSeries[] = $this->buildChartSeries(array_get($serie, 'name'), array_get($serie, 'collection'));
        }

        $chart = [
            'chart' => [
                'type' => 'column',
            ],
            'title' => ['text' => 'Responsive Defaulters'],
            'xAxis' => [
                'categories' => $this->buildChartCategories(),
            ],
            'yAxis' => [
                'min' => 0,
                'title' => ['text' => 'Customers'],
            ],
            'series' => $chartSeries,
            'credits' => ['enabled' => false],
        ];

        return $chart;
    }

    protected function buildChartSeries($name, Collection $users): array
    {
        $upto5 = 0;
        $upto10 = 0;
        $upto25 = 0;
        $upto50 = 0;
        $upto100 = 0;

        $users->load(['applications' => function($query) {
            $query->where('status', Application::STATUS_APPROVED);
        }]);

        /** @var User $user */
        foreach ($users as $user) {

            if (! $loan = $user->applications->first()) {
                continue;
            }

            $paidBack = 0;

            $application = new Application($loan);

            $payments = $application->getPayments();

            /** @var Payment $payment */
            foreach ($payments->all() as $payment) {

                if ($payment->isPaid()) {
                    $paidBack += $payment->getAmount();
                }

                $paymentParts = $payment->getParts();

                if (! $paymentParts->count()) {
                    continue;
                }

                foreach ($paymentParts as $paymentPart) {
                    $paidBack += $paymentPart->amount;
                }
            }

            $paidBackPercent = $paidBack / $loan->amount_approved * 100;

            if ($paidBackPercent >= 50) {
                $upto100++;
            }
            else if ($paidBackPercent >= 25) {
                $upto50++;
            }
            else if ($paidBackPercent >= 10) {
                $upto25++;
            }
            else if ($paidBackPercent >= 5) {
                $upto10++;
            }
            else {
                $upto5++;
            }

        }

        $totalCount = $users->count();

        return [
            'name' => $name,
            'data' => [
                [
                    'x' => 0,
                    'y' => $upto5,
                    'name' => $this->formatSerieDataNameWithTotal($name, $upto5, $totalCount),
                ],
                [
                    'x' => 1,
                    'y' => $upto10,
                    'name' =>  $this->formatSerieDataNameWithTotal($name, $upto10, $totalCount)
                ],
                [
                    'x' => 2,
                    'y' => $upto25,
                    'name' => $this->formatSerieDataNameWithTotal($name, $upto25, $totalCount)
                ],
                [
                    'x' => 3,
                    'y' => $upto50,
                    'name' => $this->formatSerieDataNameWithTotal($name, $upto50, $totalCount),
                ],
                [
                    'x' => 4,
                    'y' => $upto100,
                    'name' => $this->formatSerieDataNameWithTotal($name, $upto100, $totalCount),
                ],
            ]
        ];
    }



}