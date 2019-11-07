<?php
namespace App\NodCredit\Charts;

use App\User;
use Illuminate\Support\Collection;

abstract class Chart
{

    /**
     * @var array
     */
    protected $series;

    /**
     * @var array
     */
    protected $chartCategories = [];

    public static function generate(array $series): array
    {
        $chart = new static($series);

        return $chart->build();
    }

    /**
     * @param array $series
     */
    public function __construct(array $series)
    {
        $this->series = $series;
    }

    abstract public function build(): array;

    abstract protected function buildChartSeries($name, Collection $users);

    protected function buildChartCategories(): array
    {
        return $this->getChartCategories();
    }

    protected function getChartCategories(): array
    {
        return $this->chartCategories;
    }

    protected function getPercentOfTotal($value, $total)
    {
        return number_format($value / $total * 100, 1);
    }

    protected function formatSerieDataNameWithTotal($name, $value, $total)
    {
        return "{$this->getPercentOfTotal($value, $total)}% of total {$name} ({$total})";
    }


}