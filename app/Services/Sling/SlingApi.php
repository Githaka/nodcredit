<?php

namespace App\Services\Sling;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


/**
 * @see https://v2.sling.com.ng/api-docs#intro
 */
class SlingApi
{
    const SEND_SMS_ENDPOINT = 'https://v2.sling.com.ng/api/v1/send-sms';
    const SMS_STATUS_ENDPOINT = 'https://v2.sling.com.ng/api/v1/status/#MESSAGE_ID#';


    /** @var string */
    private $apiToken;

    /** @var string */
    private $logChannel = 'sling-requests';

    /**
     * Global to
     * @var null|string
     */
    private $to;

    /**
     * Debug mode. In debug mode it does not send request to API
     * @var bool
     */
    private $debug = false;

    /**
     * SlingApi constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->apiToken = array_get($options, 'api_token');
        $this->to = array_get($options, 'to');
        $this->debug = array_get($options, 'debug', false);

        $this->httpClient = new Client();
    }

    public function smsStatus(string $messageId)
    {
        return $this->get(str_replace('#MESSAGE_ID#', $messageId, static::SMS_STATUS_ENDPOINT));
    }

    public function sendSms(string $to, string $message, string $channel = '0000')
    {
        $options = [
            'form_params' => [
                'to' => $this->to ?: $to,
                'message' => $message,
                'channel' => $channel,
            ]
        ];

        return $this->post(static::SEND_SMS_ENDPOINT, $options);
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
     * @throws \Exception
     */
    private function request($type = 'GET', string $endpoint, array $options = [])
    {
        $options['headers']['Authorization'] = 'Bearer ' . $this->apiToken;

        $id = str_random();

        $this->logInfo("[{$id}] Request: [$type] [$endpoint]", $options);

        if ($this->debug) {
            return true;
        }

        try {
            $response = $this->httpClient->request($type, $endpoint, $options);
        }
        catch (\Exception $exception) {
            $this->logError("Exception message: {$exception->getMessage()}", ['exception' => $exception]);

            throw $exception;
        }

        $result = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $this->logInfo("[{$id}] Response: ", ['response' => $result]);

        return $result;
    }

    private function logInfo($message, array $context = []): self
    {
        return $this->log('info', $message, $context);
    }

    private function logError(string $message = '', array $context = []): self
    {
        $message = "Response: {$message}";

        return $this->log('error', $message, $context);
    }

    private function log(string $type, string $message, array $context = []): self
    {
        $message = $this->debug ? "[DEBUG MODE] " . $message : $message;

        Log::channel($this->logChannel)->{$type}($message, $context);

        return $this;
    }

}