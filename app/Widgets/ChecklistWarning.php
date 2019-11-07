<?php

namespace App\Widgets;

use App\NodCredit\Account\User;
use App\NodCredit\Settings;
use Illuminate\Contracts\Support\Htmlable;

class ChecklistWarning implements Htmlable
{

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var User
     */
    private $user;

    public function __construct(Settings $settings, string $userId = null)
    {
        $this->settings = $settings;

        if (! $userId) {
            $user = app(User::class);
        }
        else {
            $user = User::find($userId);
        }

        $this->user = $user;
    }

    public function toHtml()
    {
        $checklist = $this->user->getModel()->checkList();

        $requirements = [];
        $messages = [];

        if ($checklist) {

            foreach ($checklist as $sectionName => $section) {
                if (array_get($section, 'type') === 'requirement') {
                    $requirements[] = $section;
                }
                else if (array_get($section, 'type') === 'message') {
                    $messages[] = $section;
                }
            }
        }

        return view('widgets.checklist-warning', [
            'user' => $this->user,
            'requirements' => $requirements,
            'messages' => $messages,
            'showChecklist' => (!! $checklist AND ! $this->user->getModel()->isPartner()),
            'showAdminInform' => auth()->user()->isAdmin() AND auth()->user()->id !== $this->user->getId() AND count($requirements)
        ]);
    }
}