<?php

namespace App\Widgets;

use App\NodCredit\Loan\Application;
use App\NodCredit\Settings;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\Support\Htmlable;

class DisburseWarning implements Htmlable
{

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function toHtml()
    {
        $user = auth()->user();

        $hasCompletedLoans = $user->applications()->where('status', Application::STATUS_COMPLETED)->count();

        $hasActiveLoan = $user->applications()->where('status', Application::STATUS_APPROVED)->count();

        $newLoan = $user->applications()->where('status', Application::STATUS_NEW)->first();

        $automationActive = (int) $this->settings->get('automation_active', 0);

        return view('widgets.disburse-warning', [
            'user' => $user,
            'automationActive' => $automationActive,
            'hasCompletedLoans' => !!$hasCompletedLoans,
            'hasActiveLoan' => !!$hasActiveLoan,
            'hasNewLoan' => !!$newLoan,
            'newLoan' => $newLoan,
        ]);
    }
}