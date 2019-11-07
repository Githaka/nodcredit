<?php

namespace App\Paystack;

use App\Paystack\Exceptions\CheckAuthBrandException;
use App\Paystack\Exceptions\CreateTransferRecipientException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class PaystackApi
{
    const RESOLVE_BVN_ENDPOINT = 'https://api.paystack.co/bank/resolve_bvn/[BVN]';
    const RESOLVE_ACCOUNT_NUMBER_ENDPOINT = 'https://api.paystack.co/bank/resolve';
    const RESOLVE_CARD_BIN_ENDPOINT = 'https://api.paystack.co/decision/bin/[CARD_BIN]';
    const CHARGE_AUTHORIZATION_ENDPOINT = 'https://api.paystack.co/transaction/charge_authorization';
    const CHECK_AUTHORIZATION_ENDPOINT = 'https://api.paystack.co/transaction/check_authorization';
    const INIT_TRANSACTION_ENDPOINT = 'https://api.paystack.co/transaction/initialize';
    const VERIFY_TRANSACTION_ENDPOINT = 'https://api.paystack.co/transaction/verify/[REFERENCE]';
    const CREATE_TRANSFER_RECIPIENT_ENDPOINT = 'https://api.paystack.co/transferrecipient';
    const TRANSFER_ENDPOINT = 'https://api.paystack.co/transfer';

    private $checkAuthBrands = [
        'visa',
        'mastercard'
    ];

    /** @var string  */
    private $publicKey;

    /** @var string */
    private $secretKey;

    /** @var array */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
        $this->publicKey = array_get($options, 'public_key');
        $this->secretKey = array_get($options, 'secret_key');

        $this->httpClient = new Client();
    }

    /**
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function initTransaction(array $data)
    {
        $options = [
            'form_params' => $data
        ];

        return $this->post(static::INIT_TRANSACTION_ENDPOINT, $options);
    }

    /**
     * @param string $reference
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function verifyTransaction(string $reference)
    {
        $endpoint = str_replace('[REFERENCE]', $reference, static::VERIFY_TRANSACTION_ENDPOINT);

        return $this->get($endpoint);
    }

    /**
     * @param string $cardBin
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function resolveCardBin(string $cardBin)
    {
        $endpoint = str_replace('[CARD_BIN]', $cardBin, static::RESOLVE_CARD_BIN_ENDPOINT);

        return $this->get($endpoint);
    }

    /**
     * @param string $accountNumber
     * @param string $bankCode
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function resolveAccountNumber(string $accountNumber, string $bankCode)
    {
        $options = [
            'query' => [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ]
        ];

        return $this->get(static::RESOLVE_ACCOUNT_NUMBER_ENDPOINT, $options);
    }


    /**
     * @param string $bvn
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function resolveBvn(string $bvn)
    {
        $endpoint = str_replace('[BVN]', $bvn, static::RESOLVE_BVN_ENDPOINT);

        return $this->get($endpoint);
    }

    /**
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function chargeAuthorization(array $data)
    {
        $options = [
            'form_params' => $data
        ];

        return $this->post(static::CHARGE_AUTHORIZATION_ENDPOINT, $options);
    }

    /**
     * @param float $amountInKobo
     * @param string $authorizationCode
     * @param string $email
     * @param string $brand
     * @return bool
     * @throws CheckAuthBrandException
     */
    public function isChargeable(float $amountInKobo, string $authorizationCode, string $email, string $brand): bool
    {
        if (! $this->supportCheckAuthBrand($brand)) {
            throw new CheckAuthBrandException("Brand [{$brand}] does not support check auth request.");
        }

        try {
            $checkResponse = $this->checkAuthorization($amountInKobo, $authorizationCode, $email);
        }
        catch (\Exception $exception) {
            return false;
        }

        if (! $checkResponse->status OR $checkResponse->status !== true) {
            return false;
        }

        return true;
    }

    /**
     * @param float $amountInKobo
     * @param string $authorizationCode
     * @param string $email
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function checkAuthorization(float $amountInKobo, string $authorizationCode, string $email)
    {
        $options = [
            'form_params' => [
                'amount' => $amountInKobo,
                'authorization_code' => $authorizationCode,
                'email' => $email,
            ]
        ];

        return $this->post(static::CHECK_AUTHORIZATION_ENDPOINT, $options);
    }

    /**
     * @param string $brand
     * @return bool
     */
    public function supportCheckAuthBrand(string $brand): bool
    {
        return in_array(strtolower($brand), $this->checkAuthBrands);
    }

    public function transferToRecipient(string $recipientCode, float $amount, array $data = [])
    {
        $options = [
            'form_params' => [
                'source' => 'balance',
                'recipient' => $recipientCode,
                'amount' => intval($amount * 100),
                'currency' => array_get($data, 'currency', 'NGN') ,
                'reason' => array_get($data, 'description', '') ,
                'metadata' => array_get($data, 'metadata', [])
            ]
        ];

        return $this->post(static::TRANSFER_ENDPOINT, $options);
    }
    
    public function transfer(float $amount, string $name, string $accountNumber, string $bankCode, array $data = [])
    {
        try {
            $recipientRequest = $this->createTransferRecipient($name, $accountNumber, $bankCode, $data);
        }
        catch (ClientException $exception) {
            $json = json_decode($exception->getResponse()->getBody()->getContents());

            $message = $json ? $json->message : 'Unable to create transfer recipient.';

            throw new CreateTransferRecipientException($message);
        }

        if (! $recipientRequest->status) {
            throw new CreateTransferRecipientException('Unable to create transfer recipient. ' . $recipientRequest->message);
        }

        return $this->transferToRecipient($recipientRequest->data->recipient_code, $amount, $data);
    }

    /**
     * @param string $name
     * @param string $accountNumber
     * @param string $bankCode
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function createTransferRecipient(string $name, string $accountNumber, string $bankCode, array $data = [])
    {
        $options = [
            'form_params' => [
                'type' => 'nuban',
                'name' => $name,
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
                'currency' => array_get($data, 'currency', 'NGN') ,
                'description' => array_get($data, 'description', '') ,
                'metadata' => array_get($data, 'metadata', [])
            ]
        ];

        return $this->post(static::CREATE_TRANSFER_RECIPIENT_ENDPOINT, $options);
    }
    
    /**
     * @param string $endpoint
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function get(string $endpoint, array $options = [])
    {
        return $this->request('GET', $endpoint, $options);
    }

    /**
     * @param string $endpoint
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function post(string $endpoint, array $options = [])
    {
        return $this->request('POST', $endpoint, $options);
    }

    /**
     * @param string $type
     * @param string $endpoint
     * @param array $options
     * @return mixed
     */
    private function request($type = 'GET', string $endpoint, array $options = [])
    {
        $options['headers']['Authorization'] = 'Bearer ' . $this->secretKey;

        $this->logRequest($type, $endpoint, $options);

        $response = $this->httpClient->request($type, $endpoint, $options);

        $result = \GuzzleHttp\json_decode($response->getBody()->getContents());

        return $result;
    }

    private function logRequest($type, $endpoint, $options): self
    {
        $message = "Request: [$type] [$endpoint]";

        return $this->log($message, $options);
    }

    private function log(string $message, array $context = []): self
    {
        Log::channel('paystack-requests')->info($message, $context);

        return $this;
    }

}