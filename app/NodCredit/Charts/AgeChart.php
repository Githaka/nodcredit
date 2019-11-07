<?php
namespace App\NodCredit\Charts;

use App\User;
use Illuminate\Support\Collection;

class AgeChart extends Chart
{
    /**
     * @var array
     */
    protected $chartCategories = [
        '0-18',
        '19-25',
        '26-30',
        '31-40',
        '41-50',
        '51-60',
        '60+',
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
            'title' => ['text' => 'Age'],
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
        $today = now();

        $group0_18 = 0;
        $group19_25 = 0;
        $group26_30 = 0;
        $group31_40 = 0;
        $group41_50 = 0;
        $group51_60 = 0;
        $group61 = 0;

        /** @var User $user */
        foreach ($users as $user) {

            if (! $user->dob) {
                continue;
            }

            $years = $today->diffInYears($user->dob);

            if ($years > 60) {
                $group61++;
            }
            else if ($years > 50) {
                $group51_60++;
            }
            else if ($years > 40) {
                $group41_50++;
            }
            else if ($years > 30) {
                $group31_40++;
            }
            else if ($years > 25) {
                $group26_30++;
            }
            else if ($years > 18) {
                $group19_25++;
            }
            else if ($years > 0) {
                $group0_18++;
            }
        }

        $totalCount = $users->count();

        return [
            'name' => $name,
            'data' => [
                [
                    'x'=> 0,
                    'y' => $group0_18,
                    'name' => $this->formatSerieDataNameWithTotal($name, $group0_18, $totalCount),
                ],
                [
                    'x'=> 1,
                    'y' => $group19_25,
                    'name' =>  $this->formatSerieDataNameWithTotal($name, $group19_25, $totalCount)
                ],
                [
                    'x'=> 2,
                    'y' => $group26_30,
                    'name' => $this->formatSerieDataNameWithTotal($name, $group26_30, $totalCount)
                ],
                [
                    'x'=> 3,
                    'y' => $group31_40,
                    'name' => $this->formatSerieDataNameWithTotal($name, $group31_40, $totalCount),
                ],
                [
                    'x'=> 4,
                    'y' => $group41_50,
                    'name' => $this->formatSerieDataNameWithTotal($name, $group41_50, $totalCount),
                ],
                [
                    'x'=> 5,
                    'y' => $group51_60,
                    'name' => $this->formatSerieDataNameWithTotal($name, $group51_60, $totalCount),
                ],
                [
                    'x'=> 6,
                    'y' => $group61,
                    'name' => $this->formatSerieDataNameWithTotal($name, $group61, $totalCount),
                ],
            ]
        ];
    }



}