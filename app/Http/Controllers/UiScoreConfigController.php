<?php

namespace App\Http\Controllers;

use App\ScoreConfig;
use Illuminate\Http\Request;

class UiScoreConfigController extends Controller
{
    public function index()
    {
        return view('admin.score-config')
                    ->with('scoreConfigs', ScoreConfig::get());
    }

    public function store(Request $request)
    {
        $config = ScoreConfig::findOrFail(\request('id'));

        if(request('type') === 'flat')
        {
            $config->score  = doubleval(\request('score'));
        }
        else
        {
            $parsedConfig = $this->parseNestedConfig($request);
            if(!$parsedConfig) return back()->with('error', 'The submitted data is not valid');
            $config->frequencies = json_encode($parsedConfig);

        }

        $config->save();
        return back()->with('success', 'Config saved');

    }

    private function parseNestedConfig($request)
    {

        $output = [];
        $scores  = $request->input('score');
        if(!is_array($scores)) return false;
        if($request->input('config_type') === 'between')
        {
            $between = $request->input('between');
            if(!is_array($between)) return false;
            if(count($between) !== count($scores)) return false;
            for($i=0; $i< count($between); $i++)
            {
                $output[] = ['between' => $between[$i], 'score' => doubleval($scores[$i])];
            }

            return $output;
        }elseif($request->input('config_type') === 'amount')
        {
            $amount = $request->input('amount');
            if(!is_array($amount)) return false;
            if(count($amount) !== count($scores)) return false;
            for($i=0; $i< count($amount); $i++)
            {
                $output[] = ['amount' => $amount[$i], 'score' => doubleval($scores[$i])];
            }
            return $output;
        }

        return false;
    }
}
