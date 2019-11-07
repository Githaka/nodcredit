<?php

namespace App\NodCredit\Loan\Collections;

use App\LoanApplication as Model;
use App\NodCredit\Contracts\Transformable;
use App\NodCredit\Helpers\BaseCollection;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Document;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApplicationCollection extends BaseCollection implements Transformable
{

    public static function findNewAndReady(): self
    {
        $date = now()->subDay();
        $confirmedDate = now()->subDays(4);

        $builder = Model::where('status', Application::STATUS_NEW)
            ->where('required_documents_uploaded', true)
            ->where(function ($query) use ($date, $confirmedDate) {
                $query->where('created_at', '>', $date);
                $query->orWhere('handling_confirmed_at', '>', $confirmedDate);
            })
            ->orderBy('created_at', 'ASC')
        ;

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    /**
     * "Existing users": have one or more completed loans
     * @return ApplicationCollection
     */
    public static function findNewAndReadyForExistingUsers(): self
    {
        $date = now()->subDay();
        $confirmedDate = now()->subDays(4);

        $usersId = static::getExistingUsersId();

        $builder = Model::where('status', Application::STATUS_NEW)
            ->whereIn('user_id', $usersId)
            ->where('required_documents_uploaded', true)
            ->where(function ($query) use ($date, $confirmedDate) {
                $query->where('created_at', '>', $date);
                $query->orWhere('handling_confirmed_at', '>', $confirmedDate);
            })
            ->orderBy('created_at', 'ASC')
        ;

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findOldAndReady(int $daysOldFrom = 1, int $daysOldTo = 7): self
    {
        $startDate = now()->subDays($daysOldTo);
        $endDate = now()->subDays($daysOldFrom);

        $builder = Model::where('status', Application::STATUS_NEW)
            ->whereNull('handling_confirmation_sent_at')
            ->whereNull('handling_confirmed_at')
            ->where('required_documents_uploaded', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'ASC')
        ;

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findOldAndReadyForExistingUsers(int $daysOldFrom = 1, int $daysOldTo = 7): self
    {
        $startDate = now()->subDays($daysOldTo);
        $endDate = now()->subDays($daysOldFrom);

        $usersId = static::getExistingUsersId();

        $builder = Model::where('status', Application::STATUS_NEW)
            ->whereIn('user_id', $usersId)
            ->whereNull('handling_confirmation_sent_at')
            ->whereNull('handling_confirmed_at')
            ->where('required_documents_uploaded', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'ASC')
        ;

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findReadyForPayOut(): self
    {
        $collection = new static();

        $models = Model::where('status', Application::STATUS_APPROVAL)
            ->where('amount_allowed', '>', 0)
            ->where('approval_at', '<=', now()->subMinutes(15))
            ->whereNull('paid_out')
            ->get()
        ;

        foreach ($models as $model) {
            $collection->push(new Application($model));
        }

        return $collection;
    }

    public static function findReadyForPayOutForExistingUsers(): self
    {
        $usersId = static::getExistingUsersId();

        $builder = Model::where('status', Application::STATUS_APPROVAL)
            ->whereIn('user_id', $usersId)
            ->where('amount_allowed', '>', 0)
            ->where('approval_at', '<=', now()->subMinutes(15))
            ->whereNull('paid_out')
            ->orderBy('created_at')
        ;

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findProcessingAndHandledByParser(): self
    {

        $collection = new static();

        $models = Model::where('status', Application::STATUS_PROCESSING)
            ->where('required_documents_uploaded', true)
            ->whereHas('documents', function($query) {
                $query->where('description', 'Bank Statement');
                $query->where('parser_status', Document::PARSER_STATUS_HANDLED);
            })
            ->get()
        ;

        foreach ($models as $model) {
            $collection->push(new Application($model));
        }

        return $collection;
    }

    public static function findProcessingAndHandledByParserForExistingUsers(): self
    {
        $usersId = static::getExistingUsersId();

        $builder = Model::where('status', Application::STATUS_PROCESSING)
            ->whereIn('user_id', $usersId)
            ->where('required_documents_uploaded', true)
            ->whereHas('documents', function($query) {
                $query->where('description', 'Bank Statement');
                $query->where('parser_status', Document::PARSER_STATUS_HANDLED);
            })
            ->orderBy('created_at', 'ASC')
        ;

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findByUserId(string $id): self
    {

        $models = Model::where('user_id', $id)->orderBy('created_at', 'DESC')->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findNewAndHandlingConfirmedMoreThan(int $days = 0): self
    {
        $confirmedDate = now()->subDays($days);

        $builder = Model::where('status', Application::STATUS_NEW)
            ->where('handling_confirmed_at', '<=', $confirmedDate)
            ->orderBy('created_at', 'ASC')
        ;

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findNewAndHandlingNotConfirmedMoreThan(int $days = 0): self
    {
        $sentAt = now()->subDays($days);

        $builder = Model::where('status', Application::STATUS_NEW)
            ->where('handling_confirmation_sent_at', '<=', $sentAt)
            ->whereNull('handling_confirmed_at')
            ->whereNull('handling_rejected_at')
            ->orderBy('created_at', 'ASC')
        ;

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new Application($model));
        }

        return $collection;
    }

    public function push(Application $application): self
    {
        $this->items->push($application);

        return $this;
    }

    public function transform(): array
    {
        $result = [];

        /** @var Application $application */
        foreach ($this->all() as $application) {
            $result[] = $application->transform();
        }

        return $result;
    }

    /**
     * Get users ID which have at least one completed loan
     * @return array
     */
    public static function getExistingUsersId(): array
    {
        return DB::table('loan_applications')
            ->where('status', Application::STATUS_COMPLETED)
            ->select(['user_id'])
            ->distinct()
            ->get()
            ->pluck('user_id')
            ->toArray()
        ;

    }
}