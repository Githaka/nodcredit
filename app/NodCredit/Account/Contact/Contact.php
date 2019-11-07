<?php

namespace App\NodCredit\Account\Contact;

use App\Models\UserContact as Model;
use App\Models\UserContactEmail;
use App\Models\UserContactPhone;
use Illuminate\Database\Eloquent\Collection;

class Contact
{

    /**
     * @var Model
     */
    private $model;

    /**
     * @var UserContactPhone[]
     * @var Collection
     */
    private $phones;

    /**
     * @var UserContactPhone[]
     * @var Collection
     */
    private $activePhones;

    /**
     * @var UserContactPhone[]
     * @var Collection
     */
    private $trashedPhones;

    /**
     * @var UserContactEmail[]
     * @var Collection
     */
    private $emails;

    /**
     * @var UserContactEmail[]
     * @var Collection
     */
    private $trashedEmails;

    /**
     * @var UserContactEmail[]
     * @var Collection
     */
    private $activeEmails;

    public static function find(string $id)
    {
        $model = Model::find($id);

        if (! $model) {
            return null;
        }

        return new static($model);
    }

    /**
     * User constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        if ($model->phones) {
            $this->phones = $model->phones;
        }

        if ($model->emails) {
            $this->emails = $model->emails;
        }
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getId(): string
    {
        return $this->model->id;
    }

    public function getUserId(): string
    {
        return $this->model->user_id;
    }

    /**
     * @return string|null
     */
    public function getExternalId()
    {
        return $this->model->external_id;
    }

    /**
     * @return boolean|null
     */
    public function getStarred(): bool
    {
        return !! $this->model->starred;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->model->name;
    }

    public function getUpdatedAt()
    {
        return $this->model->updated_at;
    }

    public function getPhones(): Collection
    {
        if (! $this->phones) {
            $this->phones = $this->model->phones()->withTrashed()->orderBy('phone')->get();
        }

        return $this->phones;
    }

    public function getEmails(): Collection
    {
        if (! $this->emails) {
            $this->emails = $this->model->emails()->withTrashed()->orderBy('email')->get();
        }

        return $this->emails;
    }

    public function getTrashedEmails(): Collection
    {
        if (! $this->trashedEmails) {
            $this->trashedEmails = $this->model->emails()->onlyTrashed()->orderBy('email')->get();
        }

        return $this->trashedEmails;
    }

    public function getActiveEmails(): Collection
    {
        if (! $this->activeEmails) {
            $this->activeEmails = $this->model->emails()->orderBy('email')->get();
        }

        return $this->activeEmails;
    }

    public function getTrashedPhones(): Collection
    {
        if (! $this->trashedPhones) {
            $this->trashedPhones = $this->model->phones()->onlyTrashed()->orderBy('phone')->get();
        }

        return $this->trashedPhones;
    }

    public function getActivePhones(): Collection
    {
        if (! $this->activePhones) {
            $this->activePhones= $this->model->phones()->orderBy('phone')->get();
        }

        return $this->activePhones;
    }

    public function delete()
    {
        return $this->model->delete();
    }

    public function trashed(): bool
    {
        return $this->model->trashed();
    }
}