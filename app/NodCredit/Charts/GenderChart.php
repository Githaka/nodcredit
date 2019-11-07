<?php
namespace App\NodCredit\Charts;

use App\User;
use Illuminate\Support\Collection;

class GenderChart extends Chart
{

    /**
     * @var array
     */
    protected $chartCategories = [
        'Male',
        'Female',
        'Others'
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
            'title' => ['text' => 'Sex'],
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
        $maleCount = 0;
        $femaleCount = 0;
        $othersCount = 0;

        /** @var User $user */
        foreach ($users as $user) {
            if ($user->isMale()) {
                $maleCount++;
            }
            elseif ($user->isFemale()) {
                $femaleCount++;
            }
            elseif ($user->isOthers()) {
                $othersCount++;
            }
        }

        $totalCount = $users->count();

        return [
            'name' => $name,
            'data' => [
                [
                    'x'=> 0,
                    'y' => $maleCount,
                    'name' => $this->formatSerieDataNameWithTotal($name, $maleCount, $totalCount),
                ],
                [
                    'x'=> 1,
                    'y' => $femaleCount,
                    'name' => $this->formatSerieDataNameWithTotal($name, $femaleCount, $totalCount),
                ],
                [
                    'x'=> 2,
                    'y' => $othersCount,
                    'name' => $this->formatSerieDataNameWithTotal($name, $othersCount, $totalCount),
                ],
            ]
        ];
    }

}