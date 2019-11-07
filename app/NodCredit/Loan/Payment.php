<?php
namespace App\NodCredit\Loan;


use App\LoanPayment as Model;
use App\NodCredit\Loan\Transformers\PaymentTransformer;

use App\User;
use App\NodCredit\Account\User as AccountUser;
use App\UserCard;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Payment
{
    const STATUS_PAID = 'paid';
    const STATUS_SCHEDULED = 'scheduled';

    /**
     * @var Model
     */
    private $model;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var PaymentCharge
     */
    private $charger;

    public static function find(string $id): self
    {
        $model = Model::findOrFail($id);

        return new static($model);
    }

    /**
     * Payment constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        $this->charger = new PaymentCharge($this);
    }

    public function getUser()
    {
        if (! $this->user) {
            $this->user = $this->model->loan->owner;
        }

        return $this->user;
    }

    public function getAccountUser(): AccountUser
    {
        return new AccountUser($this->getUser());
    }

    public function getAmount(): float
    {
        return floatval($this->model->amount);
    }

    public function getId(): string
    {
        return $this->model->id;
    }

    /**
     * @return Carbon
     */
    public function getDueAt()
    {
        return $this->model->due_at;
    }

    public function getStatus(): string
    {
        return $this->model->status;
    }

    public function getCreatedAt()
    {
        return $this->model->created_at;
    }

    public function getPaymentMonth()
    {
        return $this->model->payment_month;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getApplication()
    {
        if (! $this->application) {
            $this->application = Application::find($this->getApplicationId());
        }

        return $this->application;
    }

    public function getApplicationId(): string
    {
        return $this->model->loan_application_id;
    }

    public function shiftDueAtForMonth()
    {
        $this->model->due_at = $this->model->due_at->addMonth();

        return $this->model->save();
    }

    public function increaseAmountBy($value = 0, string $type = 'percent')
    {
        if ($type === 'percent') {
            $newAmount = $this->model->amount * (100 + $value) / 100;
        }
        else if ($type === 'fixed') {
            $newAmount = $this->model->amount + $value;
        }
        else {
            throw new \Exception("Payment Increase Amount type [$type] not supported.");
        }

        $this->model->amount = $newAmount;

        return $this->model->save();
    }

    public function increaseDueCount(int $count = 1)
    {
        $this->model->due_count += $count;

        return $this->model->save();
    }

    public function paid()
    {
        $this->model->status = static::STATUS_PAID;

        return $this->model->save();
    }

    public function paidAndCheckLoan()
    {
        $this->paid();

        $this->getApplication()->getModel()->checkForCompletedLoanRepayment();
    }

    public function isPaid(): bool
    {
        return $this->model->status === static::STATUS_PAID;
    }

    public function needToChargeForPause(): bool
    {
        return !! $this->model->need_to_charge_for_pause;
    }

    public function setNeedToChargeForPause(bool $value = true)
    {
        $this->model->need_to_charge_for_pause = $value;

        return $this->model->save();
    }

    public function chargeUsingCard(float $amount = null, UserCard $card, User $performedBy = null): bool
    {
        try {
            return $this->charger->chargeUsingCard($amount, $card, $performedBy);
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    public function chargeUsingAllCards(float $amount = null): bool
    {
        try {
            return $this->charger->chargeUsingAllCards($amount);
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    public function createPartPaymentAndDeductAmount($amount)
    {
        $paymentPart = $this->model->parts()->create(['amount' => $amount]);

        $this->model->amount -= $amount;
        $this->model->save();

        if ($this->model->amount <= 0) {
            $this->paidAndCheckLoan();
        }
    }

    public function getParts()
    {
        return $this->model->parts()->orderBy('created_at')->get();
    }

    public function getTransactionLogs(): Collection
    {
        return $this->model->transactionLogs()->orderBy('created_at', 'DESC')->get();
    }

    public function deductAmount(float $amount)
    {

        $this->model->amount -= $amount;
        $this->model->save();

        if ($this->model->amount <= 0) {
            $this->paidAndCheckLoan();
        }
    }

    public function transform(): array
    {
        return PaymentTransformer::transform($this);
    }

    public function pausePenaltyFor(int $days, AccountUser $by): self
    {
        $until = now()->addDays($days)->endOfDay();

        return $this->pausePenaltyUntil($until, $by);
    }

    public function pausePenaltyUntil(Carbon $until, AccountUser $by): self
    {
        $this->model->penalty_paused_until = $until->endOfDay();
        $this->model->penalty_paused_by = $by->getId();
        $this->model->save();

        return $this;
    }

    public function resetPenaltyPause(): self
    {
        $this->model->penalty_paused_until = null;
        $this->model->save();

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->model->isDefault();
    }

    public function isPenaltyPaused(): bool
    {
        return $this->model->isPenaltyPaused();
    }
}