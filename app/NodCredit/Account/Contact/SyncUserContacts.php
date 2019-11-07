<?php

namespace App\NodCredit\Account\Contact;

use App\Models\UserContact;
use App\Models\UserContactEmail;
use App\Models\UserContactPhone;
use App\NodCredit\Account\Collections\ContactCollection;
use App\NodCredit\Account\User;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;

class SyncUserContacts
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var array
     */
    private $records;

    /**
     * @param User $user
     * @param array $records
     * @return bool
     * @throws \Exception
     */
    public static function sync(User $user, array $records): bool
    {
        $sync = new static($user);

        return $sync->handle($records);
    }

    /**
     * SyncUserContacts constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(array $records): bool
    {
        $newContactRows = [];
        $newContactEmailRows = [];
        $newContactPhoneRows = [];

        $externalIds = [];

        foreach ($records as $record) {
            if ($externalId = array_get($record, 'external_id')) {
                $externalIds[] = $externalId;
            }
        }

        // Soft delete contacts
        UserContact::whereNotIn('external_id', $externalIds)
            ->where('user_id', $this->user->getId())
            ->update(['deleted_at' => now()]);

        // Restore if they were deleted earlier
        UserContact::onlyTrashed()
            ->whereIn('external_id', $externalIds)
            ->where('user_id', $this->user->getId())
            ->update(['deleted_at' => null]);

        // Load exist Contacts, key by external id
        $existContacts = UserContact::where('user_id', $this->user->getId())
            ->get()
            ->keyBy('external_id')
        ;

        foreach ($records as $record) {
            $externalId = array_get($record, 'external_id');
            $name = array_get($record, 'name');
            $starred = array_get($record, 'starred');
            $inVisibleGroup = array_get($record, 'in_visible_group');
            $emails = array_get($record, 'emails', []);
            $phones = array_get($record, 'phones', []);

            // Update
            if ($existContact = $existContacts->get($externalId)) {

                $existContact->update([
                    'name' => $name,
                    'starred' => $starred,
                    'in_visible_group' => $inVisibleGroup,
                    'payload' => serialize($record),
                ]);

                $newContactEmailRows = array_merge($newContactEmailRows, static::syncContactEmails($existContact, $emails));

                $newContactPhoneRows = array_merge($newContactPhoneRows, static::syncContactPhones($existContact, $phones));
            }
            // Create Contact, Contact Emails and Contact Phones
            else {
                $contactId = Uuid::generate()->string;

                $newContactRows[] = [
                    'id' => $contactId,
                    'external_id' => $externalId,
                    'name' => $name,
                    'starred' => $starred,
                    'in_visible_group' => $inVisibleGroup,
                    'payload' => serialize($record),
                    'user_id' => $this->user->getId(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                foreach ($emails as $email) {
                    $newContactEmailRows[] = static::buildNewEmailQueryRecord($contactId, $email);
                }

                foreach ($phones as $phone) {
                    $newContactPhoneRows[] = static::buildNewPhoneQueryRecord($contactId, $phone);
                }
            }
        }

        // Insert new contacts
        $newContactChunkedRecords = array_chunk($newContactRows, 1000);

        foreach ($newContactChunkedRecords as $chunkRecords) {
            UserContact::insert($chunkRecords);
        }

        // Insert new contacts emails
        $newEmailChunkedRecords = array_chunk($newContactEmailRows, 1000);

        foreach ($newEmailChunkedRecords as $chunkRecords) {
            UserContactEmail::insert($chunkRecords);
        }

        // Insert new contacts phones
        $newPhoneChunkedRecords = array_chunk($newContactPhoneRows, 1000);

        foreach ($newPhoneChunkedRecords as $chunkRecords) {
            UserContactPhone::insert($chunkRecords);
        }

        return true;
    }

    public function syncContactEmails(UserContact $contact, array $emails = []): array
    {
        $newContactEmailRows = [];

        // Soft delete emails
        UserContactEmail::where('contact_id', $contact->id)
            ->whereNotIn('email', $emails)
            ->update(['deleted_at' => now()]);

        // Restore if they were deleted earlier
        UserContactEmail::onlyTrashed()
            ->where('contact_id', $contact->id)
            ->whereIn('email', $emails)
            ->update(['deleted_at' => null]);

        $contact->load('emails');

        foreach ($emails as $email) {
            $key = $contact->emails->search(function(UserContactEmail $emailModel) use ($email) {
                return $emailModel->email === $email;
            });

            // Add new
            if ($key === false) {
                $newContactEmailRows[] = static::buildNewEmailQueryRecord($contact->id, $email);
            }
        }

        return $newContactEmailRows;
    }

    public function syncContactPhones(UserContact $contact, array $phones = []): array
    {
        $newContactPhoneRows = [];

        // Soft delete phones
        UserContactPhone::where('contact_id', $contact->id)
            ->whereNotIn('phone', $phones)
            ->update(['deleted_at' => now()]);

        // Restore if they were deleted earlier
        UserContactPhone::onlyTrashed()
            ->where('contact_id', $contact->id)
            ->whereIn('phone', $phones)
            ->update(['deleted_at' => null]);

        $contact->load('phones');

        foreach ($phones as $phone) {
            $key = $contact->phones->search(function(UserContactPhone $phoneModel) use ($phone) {
                return $phoneModel->phone === $phone;
            });

            // Add new
            if ($key === false) {
                $newContactPhoneRows[] = static::buildNewPhoneQueryRecord($contact->id, $phone);
            }
        }

        return $newContactPhoneRows;
    }

    private function buildNewPhoneQueryRecord(string $contactId, string $phone): array
    {
        return [
            'id' => Uuid::generate()->string,
            'contact_id' => $contactId,
            'phone' => $phone,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function buildNewEmailQueryRecord(string $contactId, string $email): array
    {
        return [
            'id' => Uuid::generate()->string,
            'contact_id' => $contactId,
            'email' => $email,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
