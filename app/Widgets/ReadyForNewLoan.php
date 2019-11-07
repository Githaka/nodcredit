<?php

namespace App\Widgets;

use App\NodCredit\Account\User;
use App\NodCredit\Settings;
use Illuminate\Contracts\Support\Htmlable;

class ReadyForNewLoan implements Htmlable
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function toHtml()
    {
        return view('widgets.ready-for-new-loan', [
            'user' => $this->user,
        ]);
    }
}