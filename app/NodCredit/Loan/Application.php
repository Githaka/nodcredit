<?php
namespace App\NodCredit\Loan;

use App\Events\LoanApplicationDeleted;
use App\LoanApplication as EloquentModel;
use App\LoanApplication;
use App\NodCredit\Contracts\Transformable;
use App\NodCredit\Loan\Application\StatusChanger;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\Exceptions\ApplicationPauseException;
use App\NodCredit\Loan\Exceptions\ApplicationStatusChangeException;
use App\NodCredit\Loan\Transformers\ApplicationTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\NodCredit\Account\User as AccountUser;
class Application implements Transformable
{

    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_WAITING = 'waiting';
    const STATUS_APPROVAL = 'approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    /**
     * @var EloquentModel
     */
    private $model;

    /**
     * @var User
     */
    private $user;

    /**
     * @var AccountUser
     */
    private $accountUser;

    public static function find(string $id): self
    {
        $model = EloquentModel::findOrFail($id);

        return new static($model);
    }

    public static function create(array $data): self
    {
        $model = EloquentModel::create([
            'user_id' => array_get($data, 'user_id'),
            'amount_requested' => (float) array_get($data, 'amount_requested', 0),
            'amount_approved' => (float) array_get($data, 'amount_approved', 0),
            'loan_type_id' => array_get($data, 'loan_type_id'),
            'tenor' => (int) array_get($data, 'tenor', 1),
            'interest_rate' => (float) array_get($data, 'interest_rate'),
            'status' => static::STATUS_NEW
        ]);

        return new static($model);
    }

    public function __construct(EloquentModel $model)
    {
        $this->model = $model;
        $this->statusChanger = new StatusChanger();
    }

    public function getModel(): LoanApplication
    {
        return $this->model;
    }

    public function getUser(): User
    {
        if (! $this->user) {
            $this->user = $this->model->owner()->first();
        }

        return $this->user;
    }

    public function getAccountUser(): AccountUser
    {
        if (! $this->accountUser) {
            $this->accountUser = new AccountUser($this->getUser());
        }

        return $this->accountUser;
    }

    public function getUserId(): string
    {
        return $this->model->user_id;
    }


    public function getId(): string
    {
        return $this->model->id;
    }

    public function getAmountRequested(): float
    {
        return floatval($this->model->amount_requested);
    }

    public function getAmountApproved(): float
    {
        return floatval($this->model->amount_approved);
    }

    public function getAmountAllowed(): float
    {
        return floatval($this->model->amount_allowed);
    }

    public function getCreatedAt(): Carbon
    {
        return $this->model->created_at;
    }

    public function getTenor()
    {
        return (int) $this->model->tenor;
    }

    public function getInterestRate()
    {
        return (float) $this->model->interest_rate;
    }

    public function getStatus()
    {
        return $this->model->status;
    }

    public function getLoanTypeId()
    {
        return $this->model->loan_type_id;
    }

    public function getUserPauseCount(): int
    {
        return (int) $this->model->user_pause_count;
    }

    public function getUserPausedAt()
    {
        return $this->model->user_paused_at;
    }

    public function getDocuments(): Collection
    {
        return $this->model->documents()->get();
    }

    /**
     * @return Document|null
     */
    public function getBankStatementDocument()
    {
        $documents = $this->getDocuments();

        foreach ($documents as $document) {
            if ($document->description !== 'Bank Statement') {
                continue;
            }

            return new Document($document);
        }

        return null;
    }

    public function getNextPayment()
    {
        return $this->getScheduledPayments()->first();
    }

    public function isNew(): bool
    {
        return $this->model->status === static::STATUS_NEW;
    }

    public function isProcessing(): bool
    {
        return $this->model->status === static::STATUS_PROCESSING;
    }

    public function isWaiting(): bool
    {
        return $this->model->status === static::STATUS_WAITING;
    }

    public function isApproval(): bool
    {
        return $this->model->status === static::STATUS_APPROVAL;
    }

    /**
     * @param bool $dispatchEvent
     * @return bool
     * @throws ApplicationStatusChangeException
     */
    public function reject(bool $dispatchEvent = true): bool
    {
        if (! $this->statusChanger->canChange($this->model->status, static::STATUS_REJECTED)) {
            throw new ApplicationStatusChangeException('Can`t change Application status from "' . $this->model->status. '" to "' . static::STATUS_REJECTED . '".');
        }

        try {
            $this->model->status = static::STATUS_REJECTED;

            $saved = $this->model->save();
        }
        catch (\Exception $exception) {
            return false;
        }

        // Dispatch event
        if ($dispatchEvent) {
            event(new LoanApplicationDeleted($this->getModel()));
        }

        return $saved;
    }

    /**
     * @return bool
     * @throws ApplicationStatusChangeException
     */
    public function processing(): bool
    {
        if (! $this->statusChanger->canChange($this->model->status, static::STATUS_PROCESSING)) {
            throw new ApplicationStatusChangeException('Can`t change Application status from "' . $this->model->status. '" to "' . static::STATUS_PROCESSING . '".');
        }

        try {
            $this->model->status = static::STATUS_PROCESSING;

            return $this->model->save();
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return bool
     * @throws ApplicationStatusChangeException
     */
    public function waiting(): bool
    {
        if (! $this->statusChanger->canChange($this->model->status, static::STATUS_WAITING)) {
            throw new ApplicationStatusChangeException('Can`t change Application status from "' . $this->model->status. '" to "' . static::STATUS_WAITING . '".');
        }

        try {
            $this->model->status = static::STATUS_WAITING;

            return $this->model->save();
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return bool
     * @throws ApplicationStatusChangeException
     */
    public function approval(): bool
    {
        if (! $this->statusChanger->canChange($this->model->status, static::STATUS_APPROVAL)) {
            throw new ApplicationStatusChangeException('Can`t change Application status from "' . $this->model->status. '" to "' . static::STATUS_APPROVAL . '".');
        }

        try {
            $this->model->status = static::STATUS_APPROVAL;
            $this->model->approval_at = now();

            return $this->model->save();
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param float $amount
     * @param User $moderator
     * @return bool
     * @throws ApplicationStatusChangeException
     */
    public function approved(float $amount, User $moderator): bool
    {
        $this->model->refresh();

        if (! $this->statusChanger->canChange($this->model->status, static::STATUS_APPROVED)) {
            throw new ApplicationStatusChangeException('Can`t change Application status from "' . $this->model->status. '" to "' . static::STATUS_APPROVED . '".');
        }

        try {
            $this->model->status = static::STATUS_APPROVED;
            $this->model->amount_approved = $amount;
            $this->model->approved_at = now();
            $this->model->approved_by = $moderator->id;

            return $this->model->save();
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return bool
     * @throws ApplicationStatusChangeException
     */
    public function new(): bool
    {
        if (! $this->statusChanger->canChange($this->model->status, static::STATUS_NEW)) {
            throw new ApplicationStatusChangeException('Can`t change Application status from "' . $this->model->status. '" to "' . static::STATUS_NEW . '".');
        }

        try {
            $this->model->status = static::STATUS_NEW;

            return $this->model->save();
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    public function setAmountAllowed(float $amount): bool
    {
        try {
            $this->model->amount_allowed = $amount;
            $this->model->amount_allowed_at = now();

            return $this->model->save();
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    public function paidOut(): bool
    {
        $this->model->paid_out = now();

        return $this->model->save();
    }

    public function resetRequiredUploadedDocuments()
    {
        $this->model->required_documents_uploaded = null;

        return $this->model->save();
    }

    public function refreshHasRequiredUploadedDocuments()
    {
        if ($this->model->hasRequiredDocuments()) {
            $this->model->required_documents_uploaded = true;
        }
        else {
            $this->model->required_documents_uploaded = null;
        }

        return $this->model->save();
    }

    public function generatePaymentsForApprovedAmount()
    {
        return $this->generatePaymentsForAmount($this->getAmountApproved());
    }

    public function generatePaymentsForAllowedAmount()
    {
        return $this->generatePaymentsForAmount($this->getAmountAllowed());
    }

    public function generatePaymentsForAmount($amount)
    {
        $this->model->payments()->delete();

        $payments = LoanApplication::buildMonthlyPayment($amount, $this->model->interest_rate,  $this->model->tenor);

        foreach ($payments as $payment) {
            $this->getModel()->payments()->create([
                'amount' => $payment['amount'],
                'due_at' => now()->addMonths($payment['month'])->endOfDay(),
                'payment_month' => $payment['month'],
                'interest' => $payment['interest']
            ]);
        }

        return true;
    }

    public function canPauseByUser(): bool
    {
        return $this->getUserPauseCount() === 0;
    }

    /**
     * @return bool
     * @throws ApplicationPauseException
     */
    public function setPausedByUser(): bool
    {
        if (! $this->canPauseByUser()) {
            throw new ApplicationPauseException('Loan pause limit is exceeded.');
        }

        $this->model->user_pause_count += 1;
        $this->model->user_paused_at = now();

        return $this->model->save();
    }

    public function getScheduledPayments(): PaymentCollection
    {
        return PaymentCollection::findScheduledByApplication($this->getId());
    }

    public function getPayments(): PaymentCollection
    {
        return PaymentCollection::findByApplication($this->getId());
    }

    public function transform(): array
    {
        return ApplicationTransformer::transform($this);
    }

    public function getHandlingConfirmationToken(): string
    {
        return $this->model->handling_confirmation_token;
    }

    public function generateHandlingConfirmationToken(): string
    {
        $this->model->handling_confirmation_token = str_random(64);
        $this->model->save();

        return $this->getHandlingConfirmationToken();
    }

    public function setHandlingConfirmationSentAt(Carbon $date = null): bool
    {
        if (! $date) {
            $date = now();
        }

        $this->model->handling_confirmation_sent_at = $date;

        return $this->model->save();
    }

    public function setHandlingConfirmedAt(Carbon $date = null): bool
    {
        if (! $date) {
            $date = now();
        }

        $this->model->handling_confirmed_at = $date;

        return $this->model->save();
    }

    public function setHandlingRejectedAt(Carbon $date = null): bool
    {
        if (! $date) {
            $date = now();
        }

        $this->model->handling_rejected_at = $date;

        return $this->model->save();
    }
}