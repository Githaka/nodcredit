<?php

namespace App\Models\Observers;

use App\Models\UserContact;
use App\Models\UserContactEmail;
use App\Models\UserContactPhone;

class UserContactOnberver
{
    public function deleted(UserContact $userContact)
    {
        UserContactEmail::whereIn('contact_id', $userContact->id)->delete();
        UserContactPhone::whereIn('contact_id', $userContact->id)->delete();

        return true;
    }
}
