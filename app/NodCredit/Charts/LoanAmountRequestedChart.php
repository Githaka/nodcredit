<?php
namespace App\NodCredit\Charts;

use App\User;
use Illuminate\Support\Collection;

class LoanAmountRequestedChart extends Chart
{

    /**
     * @var array
     */
    protected $chartCategories = [
        '10,000 - 20,000',
        '20,001 - 30,000',
        '30,001 - 40,000',
        '40,001 - 50,000',
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
            'title' => ['text' => 'Loan Amount Requested'],
            'xAxis' => [
                'categories' => $this->buildChartCategories(),
            ],
            'yAxis' => [
                'min' => 0,
                'title' => ['text' => 'Loan Applications'],
            ],
            'series' => $chartSeries,
            'credits' => ['enabled' => false],
        ];

        return $chart;
    }

    protected function buildChartCategories(): array
    {
        return array_values($this->getChartCategories());
    }

    protected function buildChartSeries($name, Collection $users): array
    {
        $users->load('applications');

        $group10_20 = 0;
        $group20_30 = 0;
        $group30_40 = 0;
        $group40_50 = 0;

        $totalCount = 0;

        /** @var User $user */
        foreach ($users as $user) {

            $totalCount += $user->applications->count();

            foreach ($user->applications as $application) {
                if ($application->amount_requested > 40000) {
                    $group40_50++;
                }
                else if ($application->amount_requested > 30000) {
                    $group30_40++;
                }
                else if ($application->amount_requested > 20000) {
                    $group20_30++;
                }
                else if ($application->amount_requested >= 10000) {
                    $group10_20++;
                }
            }
        }

        $nameOfTotal = $name . ' applications';

        return [
            'name' => $name,
            'data' => [
                [
                    'x'=> 0,
                    'y' => $group10_20,
                    'name' => $this->formatSerieDataNameWithTotal($nameOfTotal, $group10_20, $totalCount),
                ],
                [
                    'x'=> 1,
                    'y' => $group20_30,
                    'name' => $this->formatSerieDataNameWithTotal($nameOfTotal, $group20_30, $totalCount),
                ],
                [
                    'x'=> 2,
                    'y' => $group30_40,
                    'name' => $this->formatSerieDataNameWithTotal($nameOfTotal, $group30_40, $totalCount)
                ],
                [
                    'x'=> 3,
                    'y' => $group40_50,
                    'name' => $this->formatSerieDataNameWithTotal($nameOfTotal, $group40_50, $totalCount)
                ],
            ]
        ];
    }

}