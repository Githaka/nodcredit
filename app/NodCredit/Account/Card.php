<?php

namespace App\NodCredit\Account;

use App\Paystack\Exceptions\CheckAuthBrandException;
use App\Paystack\PaystackApi;
use App\UserCard;

class Card
{

    /**
     * @var UserCard
     */
    private $model;

    /**
     * @var PaystackApi
     */
    private $paystackApi;

    /**
     * Card constructor.
     * @param UserCard $model
     */
    public function __construct(UserCard $model)
    {
        $this->model = $model;

        $this->paystackApi = app(PaystackApi::class);
    }

    public function getAuthCode()
    {
        return $this->model->auth_code;
    }

    public function getBrand()
    {
        return $this->model->brand;
    }

    /**
     * @param float $amount
     * @throws CheckAuthBrandException
     * @return bool
     */
    public function isChargeable(float $amount): bool
    {
        $amountInKobo = (int) ($amount * 100);

        $email = $this->model->email ?: $this->model->user->email;

        return $this->paystackApi->isChargeable($amountInKobo, $this->getAuthCode(), $email, $this->getBrand());
    }

}