<?php

namespace App\Http\Controllers\API;

use App\Models\UserContact;
use App\Models\UserContactEmail;
use App\Models\UserContactPhone;
use App\NodCredit\Account\Contact\SyncUserContacts;
use App\NodCredit\Account\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webpatser\Uuid\Uuid;

class AccountContactController extends ApiController
{

    public function syncContacts(Request $request, User $accountUser)
    {
        $requestJson = $request->json();

        if (! $requestJson->count()) {
            return $this->errorResponse('JSON is empty');
        }

        $records = [];

        foreach ($requestJson as $item) {
            $records[] = [
                'external_id' => array_get($item, 'mId'),
                'name' => array_get($item, 'mDisplayName'),
                'starred' => array_get($item, 'mStarred'),
                'in_visible_group' => array_get($item, 'mInVisibleGroup'),
                'emails' => array_get($item, 'mEmails', []),
                'phones' => array_get($item, 'mPhoneNumbers', []),
            ];
        }

        try {
            SyncUserContacts::sync($accountUser, $records);
        }
        catch (\Exception $exception) {

            Log::channel('api-logs')->error('Error while synchronization. ' . $exception->getMessage());

            return $this->errorResponse('Error while synchronization.');
        }

        return $this->successResponse('OK');
    }


}