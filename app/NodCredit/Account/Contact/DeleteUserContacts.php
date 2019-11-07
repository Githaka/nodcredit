<?php

namespace App\NodCredit\Account\Contact;

use App\Models\UserContact;
use App\Models\UserContactEmail;
use App\Models\UserContactPhone;
use App\NodCredit\Account\User;
use Webpatser\Uuid\Uuid;

class DeleteUserContacts
{

    /**
     * @param array $ids
     * @return bool
     */
    public static function delete(array $ids): bool
    {

        if ($contactsDeleted = UserContact::whereIn('id', $ids)->delete()) {
            UserContactEmail::whereIn('contact_id', $ids)->delete();
            UserContactPhone::whereIn('contact_id', $ids)->delete();
        }

        return !!$contactsDeleted;
    }


}