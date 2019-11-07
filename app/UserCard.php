<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCard extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'currency',
        'auth_code',
        'card_number',
        'exp_month',
        'exp_year',
        'card_type',
        'brand',
        'signature',
        'reusable',
        'email',
        'bin',
        'last4',
        'bank_name',
        'disabled_at',
        'disable_message',
        'charging_paused_at',
        'charging_paused_until',
        'charging_pause_reason',
    ];

    protected $casts = [
        'reusable' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'disabled_at',
        'charging_paused_at',
        'charging_paused_until',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionLogs()
    {
        return $this->hasMany(TransactionLog::class, 'card_id');
    }

    /**
     * Valid card is:
     * - reusable
     * - issuer name must match selected bank name
     * - expire date should be valid: >= 3 months
     * - not disabled
     *
     * @return bool
     */
    public function isValid(): bool
    {
        // Reusable
        if (! $this->isReusable()) {
            return false;
        }

        // Bank name
        if (! $this->isMatchingBankName()) {
            return false;
        }

        // Expire date
        if (! $this->isValidExpireAt()) {
            return false;
        }

        // Disabled
        if ($this->isDisabled()) {
            return false;
        }

        // Valid
        return true;
    }

    public function isReusable(): bool
    {
        return $this->reusable;
    }

    public function isMatchingBankName(): bool
    {
        if (! $this->user->bank) {
            return false;
        }

        if (strtoupper($this->bank_name) !== strtoupper($this->user->bank->name)) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        $expiredAt = Carbon::createFromDate($this->exp_year, $this->exp_month)->endOfMonth();

        $validThreshold = now()->endOfMonth();

        if ($validThreshold->greaterThan($expiredAt)) {
            return true;
        }

        return false;
    }

    public function isValidExpireAt(): bool
    {
        $expiredAt = Carbon::createFromDate($this->exp_year, $this->exp_month);
        $expiredAt->endOfMonth();

        $validThreshold = now()->addMonths(3)->endOfMonth();

        if ($validThreshold->greaterThan($expiredAt)) {
            return false;
        }

        return true;
    }

    public function isChargingPaused(): bool
    {
        if (! $this->charging_paused_until) {
            return false;
        }

        return now()->lessThan($this->charging_paused_until);
    }

    public function chargingPause(Carbon $until, string $reason = null)
    {
        $this->charging_paused_at = now();
        $this->charging_paused_until = $until;
        $this->charging_pause_reason = $reason;

        return $this->save();
    }

    public function checkAndUpdateChargingPause(): self
    {
        if ($this->isChargingPaused()) {
            return $this;
        }

        $allowedCount = 1;

        $failedTransactions = $this->transactionLogs()
            ->where('status', TransactionLog::STATUS_FAILED)
            ->whereIn('gateway_response', $this->getChargingPauseMessages())
            ->where('created_at', '>', now()->subDay())
            ->orderBy('created_at', 'DESC')
            ->limit($allowedCount + 1)
            ->get();

        if ($failedTransactions->count() <= $allowedCount) {
            return $this;
        }

        $lastTransaction = $failedTransactions->first();

        $until = $lastTransaction->created_at->copy()->addDay();

        $this->chargingPause($until, $lastTransaction->gateway_response);

        return $this;
    }

    public function isDisabled(): bool
    {
        return !! $this->disabled_at;
    }

    public function disable(string $message = null): bool
    {
        $this->disabled_at = now();
        $this->disable_message = $message;

        return $this->save();
    }

    public function disableIfMessage(string $message = null)
    {
        if (! $message) {
            return false;
        }

        if (! in_array(strtolower($message), $this->getDisableMessages(true))) {
            return false;
        }

        return $this->disable($message);
    }

    private function getDisableMessages(bool $lowercase = false): array
    {
        $array = [
            'Lost Card, Pick-Up',
            'Expired card',
            'Stolen Card, Pick up',
            'Pickup card (lost card)',
            'Pick up card',
            'Lost Card',
            'Email does not match Authorization code. Authorization may be inactive or belong to a different email. Please confirm.'
        ];

        if ($lowercase) {
            $array = array_map('strtolower', $array);
        }

        return $array;
    }

    private function getChargingPauseMessages(): array
    {
        return [
            'Insufficient Funds',
            'Not sufficient funds',
        ];
    }
}
