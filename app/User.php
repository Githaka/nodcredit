<?php

namespace App;

use App\Events\SendMessage;
use App\Models\UserDevice;
use App\NodCredit\Account\Transformers\UserTransformer;
use App\NodCredit\Account\Validators\UserProfile;
use App\NodCredit\Account\Validators\UserProfileDetails;
use App\NodCredit\Loan\Application;
use App\NodCredit\Message\MessageSender;
use App\NodCredit\Settings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\MessageBag;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends BaseModel
{

    const ROLE_USER = 'user';
    const ROLE_PARTNER = 'partner';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPPORT = 'support';

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHERS = 'others';

    use EntrustUserTrait;

    public $requiredDocuments = [];

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'bvn',
        'newsletter', 'track_usage', 'gender', 'dob', 'phone_verified', 'email_verified',
        'account_number', 'account_name', 'bank', 'scores',
        'is_app_install_skipped',
        'force_change_pwd',
        'role',
        'bvn_phone'
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'scores' => 'float',
        'is_app_install_skipped' => 'boolean',
        'force_change_pwd' => 'boolean',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function devices()
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

    public function getAvatarUrlAttribute($value)
    {
        if(!$this->avatar) {
            return asset('assets/default-avatar.png');
        }

        return asset('storage/' . $this->avatar);
    }

    

    public function getBankAttribute($value)
    {
       if(!$value) return null;

       return Bank::find($value);
    }

    public function token() {
        return $this->hasOne(Token::class);
    }

    public function applications()
    {
        return $this->hasMany(LoanApplication::class);
    }

    public function works()
    {
        return $this->hasMany(WorkHistory::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function createToken() {
        $token = str_random(150);
        $expire_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $userid = $this->id;


        if($this->token) {
            $this->token->token = $token;
            $this->token->expire_at = $expire_at;
            $this->token->save();
        } else {
            $t  = new Token();
            $t->token = $token;
            $t->expire_at = $expire_at;
            $t->user_id = $this->id;
            $t->save();
        }

        return User::find($userid);
    }

    public function invalidateToken() {
        if($this->token) {
            $this->token->expire_at = date('Y-m-d H:i:s', strtotime('-26 hours'));
            $this->token->save();
        }
        return $this;
    }

    public function getValidToken() {
        $token = $this->token()->where('expire_at', '>=', date('Y-m-d H:i:s'))->first();
        if($token) return $token->token;
        return false;
    }

    public function miniPayload()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'token' => $this->token->token,
            'bvn' => $this->bvn,
            'avatar' => $this->avatar_url,
            'account_name' => $this->name,
            'account_number' => $this->account_number,
            'bank' => $this->bank,
            'roles' => $this->roles()->get(['name','display_name'])
        ];
    }


    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function card()
    {
        return $this->hasOne(UserCard::class);
    }

    public function cards()
    {
        return $this->hasMany(UserCard::class);
    }

    public function scopeInvestors($query)
    {
        return $query->where('role', 'partner');
                        //->where('balance', '>', 0);
    }

    public function getRequiredDocuments()
    {
        return $this->requiredDocuments;
    }

    public function paymentInfo()
    {
        if(!$this->bank)
        {
            $this->requiredDocuments[] = 'Bank name is not set';
            return null;
        }
        if(!$this->account_number)
        {
            $this->requiredDocuments[] = 'Bank account number is not set';
            return null;
        }

        return [
            'name' => $this->name,
            'bank' => $this->bank,
            'account_name' => $this->name,
            'account_number' => $this->account_number,
            'recipient_code' => $this->recipient_code
        ];
    }

    public static function getCheckList(User $user) {

        /** @var Settings $settings */
        $settings = app(Settings::class);
        $accountUser = new \App\NodCredit\Account\User($user);

        $output = [];

        $profile = [];
        $loan = [];

        $requiredItems = [
            ['name' => 'Full name', 'value' => $user->name, 'key' => 'name'],
            ['name' => 'Phone', 'value' => $user->phone, 'key' => 'phone'],
            ['name' => 'Name of your bank', 'value' => $user->bank, 'key' => 'bank'],
            ['name' => 'Bank account name', 'value' => $user->name, 'key' => 'account_name'],
            ['name' => 'Bank account number', 'value' => $user->account_number, 'key' => 'account_number'],
            ['name' => 'BVN', 'value' => $user->bvn, 'key' => 'bvn'],
            ['name' => 'Work History/Employment Information', 'value' => $user->works()->count(), 'key' => 'work_history'],
            ['name' => 'Debit/Credit Card. Card should be valid at least 3 months and match your banking institution', 'value' => $accountUser->hasValidCard(), 'key' => 'card'],
        ];

        foreach($requiredItems as $item) {
            if(!$item['value']) {
                $profile[] = $item;
            }
        }

        if( $profile)
            $output['profile'] = [
                'type' => 'requirement',
                'url' => route('account.profile'),
                'items' => $profile,
                'actionText' => 'Click here to update your profile'
            ];


        // loan
        $newLoan = $user->applications()->where('status', 'new')->first();

        if($newLoan) {

            $requiredDocument = $newLoan->getUploadedDocumentTypeInfo();

            $requiredLoanData = [];
            $logicCheck = ($requiredDocument['uploadedRequired'] < $requiredDocument['required']) ? false : true;
            if(!$logicCheck) {
                $requiredLoanData[] = ['name' => 'You need to upload loan documents. (' . implode(',', $requiredDocument['requiredNames']) . ')' , 'value' => $logicCheck, 'key' => 'loan_doc'];
            }


            foreach($requiredLoanData as $item) {
                if(!$item['value']) {
                    $loan[] = $item;
                }
            }

            if($loan) {
                $output['loan'] = [
                    'type' => 'requirement',
                    'url' => route('account.loans.show', $newLoan->id),
                    'items' => $loan,
                    'actionText' => 'Click here to update loan data'
                ];
            }

            //getUploadedDocumentTypeInfo
        }


        // Mobile Application installation
        if (! $accountUser->isAppInstallSkipped() AND ! $accountUser->isAppInstalled()) {
            $output['application'] = [
                'type' => 'requirement',
                'style' => 'danger',
                'url' => route('account.downloads'),
                'items' => [
                    ['name' => 'You need to install our mobile application and allow permissions']
                ],
                'actionText' => 'You need to install our mobile application and allow permissions'
            ];
        }

        return $output;
    }

    /**
     * This method return a list of check list for the user
     *
     */
    public function checkList() {
        return User::getCheckList($this);
    }

    /**
     * User can not apply for a new loan if new loan, approved loan
     */
    public function canApplyForLoan() {

        return $this->applications()->whereNotIn('status', ['rejected','unknown','completed'])->count() ? false : true;

    }

    public function checkProfileCompletion(): MessageBag
    {
        return UserProfile::checkCompletion(new \App\NodCredit\Account\User($this));
    }

    public function validateSuccessfullyAddedDetailsScore(): bool
    {
        $scoreName = 'SUCCESSFULLY_ADDED_DETAILS';

        // Profile is not completed
        if ($this->checkProfileCompletion()->any()) {
            $this->removeScore($scoreName);

            return false;
        }

        // Exists
        if ($exists = $this->hasEarnedPoint($scoreName)) {
            return true;
        }

        // Add score
        $this->getScoreInfo($scoreName);

        return true;
    }

    /**
     * @deprecated Has bug. Rewrited.
     * A new method to check if the user has completed all their check list, here we cna reward them
     * @return bool
     */
    public function awardPointForCompletedCheckList()
    {
        $pointName = 'SUCCESSFULLY_ADDED_DETAILS';
        if(!$this->checkList() && !$this->applications()->count() && !$this->hasEarnedPoint($pointName))
        {
            $this->getScoreInfo($pointName); // Get the score info and award it
        } else {
            // if for any reason like the user has earned the point and deleted some part of the requirement, remove the point
            $this->removeScore('SUCCESSFULLY_ADDED_DETAILS');
        }
    }

    public function hasEarnedPoint($scoreName)
    {
        return $this->scores()->where('info', $scoreName)->first();
    }

    public function removeScore($scoreName)
    {
        $hasPoint = $this->hasEarnedPoint($scoreName);
        if($hasPoint)
        {
            $hasPoint->delete();
        }

        $this->calculateScoresAndHandle();
    }

    public function sendOTP(string $messageType = 'sms')
    {
        if (! in_array($messageType, ['sms', 'email', 'both'])) {
            throw new \Exception("Messages does not support type [$messageType].");
        }

        OTP::where('phone', $this->phone)->delete();
        $otp =  makeOTP(4);
        OTP::create(['expire_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), 'phone' => $this->phone, 'otp' => $otp]);
        $message = Message::create([
            'message' => 'Your NodCredit OTP: ' . $otp . '. Your OTP is secret and must not be shared with anyone else.',
            'message_type' => $messageType,
            'user_id' => $this->id,
            'sender' => $this->id,
            'subject' => 'ACTION REQUIRED: NodCredit OTP'
        ]);

        event(new SendMessage($message));
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function loanActions()
    {
        return $this->hasMany(LoanAction::class);
    }

    public function signature()
    {
        return sprintf('(%s) %s', e($this->id), e($this->name));
    }

    public function isPartner()
    {
        return $this->role === 'partner';
    }

    public function partnerUrl()
    {
        return route('account.profile.invest');
    }

    public function logs()
    {
        return $this->hasMany(NodLog::class);
    }

    /**
     * Give user some scores.
     *
     * @param      $point | double
     * @param      $reason | string
     * @param bool $claim | boolean
     * @example
     *         $this->giveScore(1.2, 'Document upload');
     * @return $this
     */
    public function giveScore($point, $reason, $claim=false)
    {

        $userTotalScore = $this->getScores();

        if($userTotalScore <= Setting::v('max_score'))
        {
            $this->scores()->create(['amount' => $point, 'info' => $reason, 'claimed' => $claim]);

            $this->calculateScoresAndHandle();
        }


        return $this; // allows method
    }

//    /**
//     * @return double
//     */
//    public function claimScores()
//    {
//        $totalScores = $this->getScores();
//
//        $this->scores += $totalScores;
//        $this->save();
//
//        $this->scores()->where('claimed', false)
//                    ->update(['updated_at' => now(), 'claimed' => true]);
//
//        return $totalScores;
//    }
//    public function useScore($howMany=0)
//    {
//        if($this->scores > 0) {
//            $this->scores -= $howMany;
//            $this->save();
//        }
//        return $this->scores;
//    }

    public function getScores()
    {
        return $this->scores()
            ->where('claimed', false)
            ->sum('amount');
    }


    public function getLastCompletedLoan()
    {
        return LoanApplication::where('status', Application::STATUS_COMPLETED)
            ->where('user_id', $this->id)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public static function getAllAdminEmails()
    {
        return explode(',', env('MAIL_TO_ADMINS'));
    }

    public function getScoreInfo($scoreInfo, $input=null)
    {
        $scoreInfo = getScoreInfo($scoreInfo, $input);
        if($scoreInfo)
        {
            $this->giveScore($scoreInfo->score, $scoreInfo->name);
        }
    }

    public function getUserLoanMinMax()
    {

        $score = $this->getScores();

        $loanRanges = LoanRange::get();

        $loanRange = null;
        foreach($loanRanges as $_range)
        {
            if($score >= $_range->min_score && $score <= $_range->max_score)
            {
                $loanRange = $_range;
                break;
            }
        }

        $output = new \stdClass();
        $output->loan_min = $loanRange ? $loanRange->min : 0;
        $output->loan_max = $loanRange ? $loanRange->max : 0;

        return $output;
    }

    public function getInterestRate()
    {
        $scoreRates = json_decode(Setting::v('score_interest_rate'));

        $rate = Setting::v('default_interest_rate');

        $score = $this->getScores();

        if($scoreRates)
        {
            foreach($scoreRates as $key=>$value)
            {
                // the key is the range, value is percent e.g 100-200 => 15
                $loanRanges = explode('-', $key);
                if(count($loanRanges) === 2)
                {
                    if($score >= $loanRanges[0] && $score <= $loanRanges[1])
                    {
                        $rate = $value;
                        break;
                    }
                }
            }
        }

        return $rate;
    }


    public function isAdmin(): bool
    {
        return $this->role === static::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === static::ROLE_USER;
    }

    public function isSupport(): bool
    {
        return $this->role === static::ROLE_SUPPORT;
    }

    /**
     * @return Collection
     * @return UserCard[]
     */
    public function getCardsForChargingBySystem()
    {
        /** @var Collection $cards */
        $cards = $this->cards()->withTrashed()->get();

        return $cards->filter(function($card) {
            /** @var UserCard $card */

            if (! $card->isReusable()) {
                return false;
            }

            if ($card->isExpired()) {
                return false;
            }

            if ($card->isDisabled()) {
                return false;
            }

            return true;
        });
    }

    public function isMale(): bool
    {
        return $this->gender === static::GENDER_MALE;
    }

    public function isFemale(): bool
    {
        return $this->gender === static::GENDER_FEMALE;
    }

    public function isOthers(): bool
    {
        return $this->gender === static::GENDER_OTHERS;
    }

    public function transform(): array
    {
        return UserTransformer::transform($this);
    }

    public function calculateScoresAndHandle(): self
    {
        $oldScores = $this->scores;

        $this->calculateScores();

        // Handle changes. Disabled for a while.
        //$this->hasReachedNewLoanRange($oldScores);

        return $this;
    }

    public function calculateScores(): self
    {
        $this->scores = $this->scores()->sum('amount');
        $this->save();

        return $this;
    }

    public function hasReachedNewLoanRange(float $oldScores, bool $sendMessage = true): bool
    {
        if ($oldScores >= $this->scores) {
            return false;
        }

        $ranges = LoanRange::all();

        $oldRange = null;
        $newRange = null;

        foreach ($ranges as $range) {
            if ($oldScores >= $range->min_score AND $oldScores <= $range->max_score) {
                $oldRange = $range;
            }

            if ($this->scores >= $range->min_score AND $this->scores <= $range->max_score) {
                $newRange = $range;
            }
        }

        if (! $oldRange OR ! $newRange) {
            return false;
        }

        if ($oldRange->id === $newRange->id) {
            return false;
        }

        if ($sendMessage === true) {
            MessageSender::send('user-loan-range-reached', new \App\NodCredit\Account\User($this), [
                '#LOAN_RANGE_MAX_AMOUNT#' => 'N' . number_format($newRange->max),
                '#LOAN_RANGE_MAX_MONTH#' => $newRange->max_month,
            ]);
        }

        return true;
    }
}
