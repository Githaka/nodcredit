<?php

namespace App\NodCredit\Account\Collections;

use App\Models\UserContact as Model;
use App\NodCredit\Account\Contact\Contact;
use App\NodCredit\Account\Contact\DeleteUserContacts;
use App\NodCredit\Helpers\BaseCollection;
use Illuminate\Database\Eloquent\Collection;

class ContactCollection extends BaseCollection
{

    public static function findByUserId(string $id, array $options = []): self
    {
        $builder = Model::where('user_id', $id);

        if (in_array('withTrashed', $options)) {
            $builder
                ->withTrashed()
                ->with([
                    'emails' => function($query) {
                        return $query->withTrashed();
                    },
                    'phones' => function($query) {
                        return $query->withTrashed();
                    },
                ]);
        }
        else {
            $builder->with(['emails', 'phones']);
        }

        $models = $builder
            ->orderBy('name', 'ASC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function countByUserId(string $id, array $options = []): int
    {
        $builder = Model::where('user_id', $id);

        if (in_array('withTrashed', $options)) {
            $builder
                ->withTrashed()
                ->with([
                    'emails' => function($query) {
                        return $query->withTrashed();
                    },
                    'phones' => function($query) {
                        return $query->withTrashed();
                    },
                ]);
        }
        else {
            $builder->with(['emails', 'phones']);
        }

        return $builder->count();
    }

    public static function deleteByUserId(string $id)
    {
        return static::findByUserId($id)->delete();
    }


    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new Contact($model));
        }

        return $collection;
    }

    /**
     * @return Contact[]
     */
    public function all(): array
    {
        return parent::all();
    }

    public function filterByActive(): self
    {
        $items = $this->items->filter(function(Contact $contact) {
            return ! $contact->trashed();
        });

        return new self($items);
    }

    public function filterByTrashed(): self
    {
        $items = $this->items->filter(function(Contact $contact) {
            return $contact->trashed();
        });

        return new self($items);
    }

    public function delete(): bool
    {
        return DeleteUserContacts::delete($this->getIds());
    }

    public function getIds(): array
    {
        $ids = [];

        foreach ($this->all() as $contact) {
            $ids[] = $contact->getId();
        }

        return $ids;
    }

    public function push(Contact $contact): self
    {
        $this->items->push($contact);

        return $this;
    }

}