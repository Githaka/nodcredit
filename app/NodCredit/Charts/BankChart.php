<?php
namespace App\NodCredit\Charts;

use App\Bank;
use App\User;
use Illuminate\Support\Collection;

class BankChart extends Chart
{

    /**
     * @var Bank[]
     */
    private $banks;

    /**
     * @param array $series
     */
    public function __construct(array $series)
    {
        parent::__construct($series);

        $this->banks = Bank::all();
    }

    public function build(): array
    {
        $chartSeries = [];

        foreach ($this->series as $serie) {
            $chartSeries[] = $this->buildChartSeries(array_get($serie, 'name'), array_get($serie, 'collection'));
        }

        $notEmptyKeys = $this->getNotEmptyDataKeysInSeries($chartSeries);

        $chartSeries = $this->filterDataInSeriesByKeys($chartSeries, $notEmptyKeys);

        $chartCategories = $this->filterArrayByKeys($this->buildChartCategories(), $notEmptyKeys);

        $chart = [
            'chart' => [
                'type' => 'bar',
                'height' => count($notEmptyKeys) * 30 + 100
            ],
            'title' => ['text' => 'Banks'],
            'xAxis' => [
                'categories' => $chartCategories,
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

    protected function buildChartCategories(): array
    {
        return array_values($this->getChartCategories());
    }

    protected function getChartCategories(): array
    {
        if (! $this->chartCategories) {
            foreach ($this->banks as $bank) {
                $this->chartCategories[$bank->id] = $bank->name;
            }
        }

        return $this->chartCategories;
    }

    protected function buildChartSeries($name, Collection $users): array
    {
        $categories = $this->getChartCategories();

        $data = array_flip($categories);

        $data = array_map(function() {
            return 0;
        }, $data);


        /** @var User $user */
        foreach ($users as $user) {
            if ($bankName = array_get($categories, $user->bank->id)) {
                $data[$bankName] = array_get($data, $bankName, 0) + 1;
            }
        }

        return [
            'name' => $name,
            'data' => array_values($data)
        ];
    }

    private function getNotEmptyDataKeysInSeries(array $series): array
    {
        $data = $series[0]['data'];
        $data = array_map(function() {
            return 0;
        }, $data);

        foreach ($series as $serie) {
            foreach ($serie['data'] as $key => $item) {
                if ($item > 0) {
                    $data[$key] += $item;
                }
            }
        }

        return array_keys(array_diff($data, [0]));
    }

    private function filterArrayByKeys(array $array, array $keys): array
    {
        $result = array_filter($array, function($key) use ($keys) {
            if (in_array($key, $keys)) {
                return true;
            }
            return false;

        }, ARRAY_FILTER_USE_KEY);

        return array_values($result);
    }

    private function filterDataInSeriesByKeys(array $series, array $keys): array
    {
        $result = [];

        foreach ($series as $serie) {
            $item = $serie;
            $item['data'] = $this->filterArrayByKeys($item['data'], $keys);

            $result[] = $item;
        }

        return $result;
    }
}