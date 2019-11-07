<?php

namespace App\NodCredit\Account;

use App\Models\UserDevice;
use App\NodCredit\Account\Collections\LocationCollection;
use App\NodCredit\Account\Location\UserLocation;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\Models\InvestmentModel;
use App\NodCredit\Loan\Application;
use App\NodCredit\Account\Collections\ContactCollection;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Account\Exceptions\DeviceLinkException;
use App\NodCredit\Settings;
use App\User as Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class User
{
    /**
     * User
     */
    private $model;

    /**
     * @var ContactCollection
     */
    private $contacts;

    /**
     * @var LocationCollection
     */
    private $locations;

    /**
     * @var Settings
     */
    private $settings;

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
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getId(): string
    {
        return $this->model->id;
    }

    public function getName(): string
    {
        return $this->model->name;
    }

    public function getEmail(): string
    {
        return $this->model->email;
    }

    public function getPhone()
    {
        return $this->model->phone;
    }

    public function getAccountNumber()
    {
        return $this->model->account_number;
    }

    public function getBankCode()
    {
        return $this->model->bank ? $this->model->bank->code : null;
    }

    public function getBankId()
    {
        return $this->model->getAttribute('bank');
    }

    public function getBvn()
    {
        return $this->model->bvn;
    }

    /**
     * Check if user has due payment(s)
     * @return bool
     */
    public function isDefaulter(): bool
    {
        return !! $this->findDuePayments()->count();
    }

    public function hasValidCard(): bool
    {
        return !! $this->getValidCards()->count();
    }

    public function countValidCard(): int
    {
        return $this->getValidCards()->count();
    }

    /**
     * Valid card is:
     * - reusable
     * - issuer name must match selected bank name
     * - expire date should be valid: >= 3 months
     */
    public function getValidCards()
    {
        if (! $this->model->bank) {
            return collect();
        }

        $validDate = now()->addMonths(3)->endOfMonth();

        $bankName =  strtoupper($this->model->bank->name);

        $cards = $this->model->cards()
            ->where('reusable', true)
            ->where('bank_name', $bankName)
            ->whereRaw(DB::raw('STR_TO_DATE(concat_ws("-", `exp_month`, `exp_year`), "%m-%Y") >= STR_TO_DATE("' . $validDate->format('m-Y') . '", "%m-%Y")'))
            ->get()
        ;

        return $cards;
    }

    public function getCards()
    {
        return $this->model->cards()->get();
    }

    public function findDuePayments(): PaymentCollection
    {
        return PaymentCollection::findDueByUserId($this->getId());
    }

    public function canApplyForLoan(): bool
    {
        // No active loan
        if ($this->hasActiveLoan()) {
            return false;
        }

        // Must has valid card
        if (! $this->hasValidCard()) {
            return false;
        }

        // Partner can`t
        if ($this->getModel()->isPartner()) {
            return false;
        }

        // If user did not skip app install requirement and did not install app
        if (! $this->isAppInstallSkipped() AND ! $this->isAppInstalled()) {
            return false;
        }

        return true;
    }

    /**
     * @return Application|null
     */
    public function getActiveLoan()
    {
        $model = $this->model->applications()
            ->whereIn('status', [
                Application::STATUS_NEW,
                Application::STATUS_APPROVED,
                Application::STATUS_PROCESSING,
                Application::STATUS_WAITING,
                Application::STATUS_APPROVAL
            ])
            ->first();

        if (! $model) {
            return null;
        }

        return new Application($model);
    }

    public function hasActiveLoan(): bool
    {
        return !! $this->getActiveLoan();
    }

    public function ban(string $reason = ''): bool
    {
        $this->model->banned_at = now();
        $this->model->ban_reason = $reason;

        return $this->model->save();
    }

    public function unban(): bool
    {
        $this->model->banned_at = null;
        $this->model->ban_reason = null;

        return $this->model->save();
    }

    public function getBannedAt()
    {
        return $this->model->banned_at;
    }

    public function isBanned(): bool
    {
        return !! $this->getBannedAt();
    }

    public function getContacts(): ContactCollection
    {
        if (! $this->contacts) {
            $this->contacts = ContactCollection::findByUserId($this->getId(), ['withTrashed']);
        }

        return $this->contacts;
    }

    public function getLocations(): LocationCollection
    {
        if (! $this->locations) {
            $this->locations = LocationCollection::findByUserId($this->getId());
        }

        return $this->locations;
    }

    /**
     * @return UserLocation|null
     */
    public function getLastLocation()
    {
        return UserLocation::findLastByUserId($this->getId());
    }

    public function isContactsAgeValid(): bool
    {
        $contacts = ContactCollection::findByUserId($this->getId());

        // No active contacts
        if (! $contacts->count()) {
            return false;
        }

        $validDate = now()->subDays($this->getNodSettings()->get('user_location_and_contact_valid_age', 60));

        if ($contacts->first()->getUpdatedAt()->lessThan($validDate)) {
            return false;
        }

        return true;
    }

    public function isLocationAndContactsAgeValid(): bool
    {
        if (! $lastLocation = $this->getLastLocation()) {
            return false;
        }

        $validDate = now()->subDays($this->getNodSettings()->get('user_location_and_contact_valid_age', 60));

        if ($lastLocation->getCreatedAt()->lessThan($validDate)) {
            return false;
        }

        if (! $this->isContactsAgeValid()) {
            return false;
        }

        return true;
    }

    public function getDevices(): Collection
    {
        return $this->model->devices()->get();
    }

    public function linkDeviceId(string $deviceId): bool
    {
        if ($exists = UserDevice::where('device_id', $deviceId)->first()) {
            if ($exists->user_id === $this->getId()) {
                return true;
            }

            throw new DeviceLinkException('Device ID belongs to another user.');
        }

        $device = UserDevice::create([
            'user_id' => $this->getId(),
            'device_id' => $deviceId,
        ]);

        return $device->exists;
    }

    public function calculateInvestBalance(): float
    {
        $balance = InvestmentModel::where('user_id', $this->getId())
            ->whereIn('status', [Investment::STATUS_ACTIVE, Investment::STATUS_NEW])
            ->sum('amount')
        ;

        $this->model->balance = $balance;
        $this->model->save();

        return floatval($balance);
    }

    public function skipAppInstall(bool $value = true): self
    {
        $this->model->is_app_install_skipped = $value;
        $this->model->save();

        return $this;
    }

    public function isAppInstallSkipped(): bool
    {
        return !! $this->model->is_app_install_skipped;
    }

    public function isAppInstalled(): bool
    {
        return $this->isContactsAgeValid();
    }

    public function isAdmin(): bool
    {
        return (bool) $this->model->isAdmin();
    }

    public function isSupport(): bool
    {
        return (bool) $this->model->isSupport();
    }

    public function isPartner(): bool
    {
        return (bool) $this->model->isPartner();
    }

    public function isUser(): bool
    {
        return (bool) $this->model->isUser();
    }

    private function getNodSettings(): Settings
    {
        if (! $this->settings) {
            $this->settings = app(Settings::class);
        }

        return $this->settings;
    }


}