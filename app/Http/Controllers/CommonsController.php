<?php

namespace App\Http\Controllers;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;
use Amenadiel\JpGraph\Themes\UniversalTheme;
use App\Bank;
use Illuminate\Http\Request;

class CommonsController extends Controller
{

    public function getBanks(Request $request)
    {
        $filter = (new Bank)->newQuery();

        if($request->input('q'))
        {
            $filter->where('name', 'like', '%' .$request->input('q') . '%')
                ->orWhere('code', 'like', '%' . $request->input('q') . '%');
        }

        return $this->successResponse('banks',$filter->get());
    }

    /**
     * @param $amount
     * @param $days
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getAmountGrowthGraph($amount, $days)
    {
        $amount = (int) $amount;
        $days = (int) $days;

        if ($days > 31) {
            $days = 31;
        }
        else if ($days < 2) {
            $days = 2;
        }

        $graph = new Graph(500, 300);

        $dataY = [];
        $dataX = [];

        // Fill days
        for ($i = 0; $i < $days; $i++) {
            $amount = number_format($amount * 1.01, 0, '', '');

            $dataY[$i] = $amount;
            $dataX[$i] = '';
        }

        $lastIndex = $days - 1;
        $dataX[0] = '+1d.';
        $half = (int) round($lastIndex / 2);
        $dataX[$half] = "+{$half}d.";
        $dataX[$lastIndex] = "+{$days}d.";

        $graph->SetScale("textlin");
        $graph->SetTheme(new UniversalTheme());
        $graph->SetBox(false);
        $graph->SetMargin(65,20,36,63);

        $graph->img->SetAntiAliasing(false);
        $graph->title->Set("$days days growth");

        $graph->img->SetAntiAliasing();

        $graph->yaxis->HideZeroLabel();
        $graph->xaxis->SetTickLabels($dataX);

        // Create the line
        $p1 = new LinePlot($dataY);
        $graph->Add($p1);
        $p1->SetColor("#FF0000");
        $p1->SetLegend('Amount, NGN');

        $graph->legend->SetFrameWeight(1);

        return response($graph->Stroke(), 200, [
            'Content-Type' => 'image/png'
        ]);
    }
}
