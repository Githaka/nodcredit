<?php
namespace App\NodCredit\Charts;

use App\LoanType;
use App\User;
use Illuminate\Support\Collection;

class LoanTypeChart extends Chart
{

    /**
     * @var LoanType[]
     */
    private $loanTypes;

    /**
     * @param array $series
     */
    public function __construct(array $series)
    {
        parent::__construct($series);

        $this->loanTypes = LoanType::all();
    }

    public function build(): array
    {
        $chartSeries = [];

        foreach ($this->series as $serie) {
            $chartSeries[] = $this->buildChartSeries(array_get($serie, 'name'), array_get($serie, 'collection'));
        }

        $chart = [
            'chart' => ['type' => 'column'],
            'title' => ['text' => 'Loan Type'],
            'xAxis' => [
                'categories' => $this->buildChartCategories(),
                'crosshair' => true
            ],
            'yAxis' => [
                'min' => 0,
                'title' => ['text' => 'Loan Applications']
            ],
            'credits' => ['enabled' => false],
            'series' => $chartSeries
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
            foreach ($this->loanTypes as $loanType) {
                $this->chartCategories[$loanType->id] = $loanType->name;
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

        $users->load('applications');

        $totalCount = 0;

        /** @var User $user */
        foreach ($users as $user) {

            $totalCount += $user->applications->count();

            $types = $user->applications->pluck('loan_type_id');

            foreach ($types as $type) {
                if ($typeName = array_get($categories, $type)) {
                    $data[$typeName] = array_get($data, $typeName, 0) + 1;
                }
            }
        }

        $data = array_values($data);

        foreach ($data as $key => $value) {
            $data[$key] = [
                'x'=> $key,
                'y' => $value,
                'name' => $this->formatSerieDataNameWithTotal($name . ' applications', $value, $totalCount),
            ];
        }

        return [
            'name' => $name,
            'data' => $data
        ];
    }

}