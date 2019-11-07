<?php

namespace App\NodCredit\Loan\Collections;

use App\LoanPayment as Model;
use App\NodCredit\Contracts\Transformable;
use App\NodCredit\Helpers\BaseCollection;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PaymentCollection extends BaseCollection implements Transformable
{

    public static function findScheduledByApplication(string $id)
    {
        $collection = new static();

        $models = Model::where('status', Model::STATUS_SCHEDULED)
            ->where('loan_application_id', $id)
            ->orderBy('payment_month', 'ASC')
            ->get();

        foreach ($models as $model) {
            $collection->push(new Payment($model));
        }

        return $collection;
    }

    public static function findByApplication(string $id): self
    {
        $models = Model::where('loan_application_id', $id)
            ->orderBy('payment_month', 'ASC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findDueIn(int $days = 0): self
    {
        $dayStart = now()->addDays($days)->startOfDay();
        $dayEnd = now()->addDays($days)->endOfDay();

        $collection = new static();

        $models = Model::with('loan.owner')
            ->where('status', Model::STATUS_SCHEDULED)
            ->whereHas('loan', function($query) {
                $query->where('status', Application::STATUS_APPROVED)->whereNotNull('paid_out');
            })
            ->whereBetween('due_at', [$dayStart, $dayEnd])
            ->get();

        foreach ($models as $model) {
            $collection->push(new Payment($model));
        }

        return $collection;
    }

    public static function findDueFor(int $days = 0, int $dueCount = null): self
    {
        $dayStart = now()->subDays($days)->startOfDay();
        $dayEnd = now()->subDays($days)->endOfDay();

        $collection = new static();

        $builder = Model::with('loan.owner')
            ->where('status', Model::STATUS_SCHEDULED)
            ->whereHas('loan', function($query) {
                $query->where('status', Application::STATUS_APPROVED)->whereNotNull('paid_out');
            })
            ->whereBetween('due_at', [$dayStart, $dayEnd]);

        if (is_int($dueCount)) {
            $builder->where('due_count', $dueCount);
        }

        $models = $builder->get();

        foreach ($models as $model) {
            $collection->push(new Payment($model));
        }

        return $collection;
    }

    public static function findForPenalties(): self
    {

        $models = Model::with('loan.owner')
            ->where('status', Model::STATUS_SCHEDULED)
            ->where('due_count', 2)

            ->where(function($query) {
                $query->where('penalty_paused_until', '<', now());
                $query->orWhereNull('penalty_paused_until');
            })
            ->whereHas('loan', function($query) {
                $query->where('status', Application::STATUS_APPROVED)->whereNotNull('paid_out');
            })
            ->get()
        ;

        return static::makeCollectionFromModels($models);
    }

    public static function findDueByDueCount(int $dueCount = 0): self
    {
        $collection = new static();

        $models = Model::with('loan.owner')
            ->where('status', Model::STATUS_SCHEDULED)
            ->where('due_count', $dueCount)
            ->whereHas('loan', function($query) {
                $query->where('status', Application::STATUS_APPROVED)->whereNotNull('paid_out');
            })
            ->get();

        foreach ($models as $model) {
            $collection->push(new Payment($model));
        }

        return $collection;
    }

    public static function findDueForOrMore(int $days = 0, int $dueCount = null): self
    {
        $date = now()->subDays($days)->endOfDay();

        $builder = Model::with('loan.owner')
            ->where('status', Model::STATUS_SCHEDULED)
            ->whereHas('loan', function($query) {
                $query->where('status', Application::STATUS_APPROVED)->whereNotNull('paid_out');
            })
            ->where('due_at', '<=', $date)
            ->orderBy('due_at')
        ;

        if (is_int($dueCount)) {
            $builder->where('due_count', $dueCount);
        }

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findDueByUserId(string $id): self
    {
        $date = now();

        $collection = new static();

        $builder = Model::select('loan_payments.id')
            ->join('loan_applications', function($join) {
                $join
                    ->on('loan_applications.id', 'loan_payments.loan_application_id')
                    ->where('loan_applications.status', Application::STATUS_APPROVED)
                    ->whereNotNull('loan_applications.paid_out');
            })
            ->join('users', function ($join) use ($id) {
                $join
                    ->on('users.id', 'loan_applications.user_id')
                    ->where('users.id', $id);
            })
            ->where('loan_payments.status', Model::STATUS_SCHEDULED)
            ->where(function ($query) use ($date) {
                $query
                    ->where('loan_payments.due_at', '<=', $date)
                    ->orWhere('loan_payments.due_count', '>', 0);
            });

        $paymentsId = $builder->get()->pluck('id');

        if ($paymentsId->count()) {

            $models = Model::whereIn('id', $paymentsId->toArray())->get();

            foreach ($models as $model) {
                $collection->push(new Payment($model));
            }
        }

        return $collection;
    }

    public static function findDueForOrLess(int $days = 1, int $dueCount = null): self
    {
        $dayStart = now()->subDays($days)->startOfDay();
        $dayEnd = now()->subDay()->endOfDay();

        $builder = Model::with('loan.owner')
            ->where('status', Model::STATUS_SCHEDULED)
            ->whereBetween('due_at', [$dayStart, $dayEnd])
            ->whereHas('loan', function($query) {
                $query
                    ->where('status', Application::STATUS_APPROVED)
                    ->whereNotNull('paid_out');
            })
            ->orderBy('due_at');

        if (is_int($dueCount)) {
            $builder->where('due_count', $dueCount);
        }

        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findNeedToChargeForPause(): self
    {
        $builder = Model::with('loan.owner')
            ->where('need_to_charge_for_pause', true)
            ->where('due_count', '=', 1)
            ->where('due_at', '>', now()->subMonth()->endOfDay())
            ->where('status', Model::STATUS_SCHEDULED)
            ->whereHas('loan', function($query) {
                $query
                    ->where('status', Application::STATUS_APPROVED)
                    ->whereNotNull('paid_out');
            })
            ->orderBy('due_at');


        $models = $builder->get();

        return static::makeCollectionFromModels($models);
    }

    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new Payment($model));
        }

        return $collection;
    }

    public function getIds(): array
    {
        $result = [];

        /** @var Payment $payment */
        foreach ($this->all() as $payment) {
            $result[] = $payment->getId();
        }

        return $result;
    }

    public function push(Payment $payment): self
    {
        $this->items->push($payment);

        return $this;
    }

    public function transform(): array
    {
        $result = [];

        /** @var Payment $payment */
        foreach ($this->all() as $payment) {
            $result[] = $payment->transform();
        }

        return $result;
    }
}