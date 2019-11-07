<?php
use Propaganistas\LaravelPhone\PhoneNumber;
use \App\LoanApplication;
use \App\LoanDocumentType;
use \App\Events\RequiredDocumentUploaded;
use \App\Events\RequiredDocumentNotUploaded;

function makePaystackPostRequest($endpoint, array $fields) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($fields),
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
            "content-type: application/json",
            "cache-control: no-cache"
        ],
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    if($err){
        return ['status' => 'error', 'message' => $err, 'data' => null];
    }

    $tranx = json_decode($response);

    return ['status' => 'ok', 'data' => $tranx, 'message' => 'ok'];
}


function makePaystackGetRequest($endpoint)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
            "cache-control: no-cache"
        ],
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);


    if($err){
        return ['status' => 'error', 'message' => $err, 'data' => null];
    }

    $tranx = json_decode($response);

    return ['status' => 'ok', 'data' => $tranx, 'message' => 'ok'];
}

function percentOf($amount, $p)
{
    return ($amount / 100) * $p;
}

function get_setting($k)
{
    return \App\Setting::v($k);
}

function formatPhone($phone, $countryCode = 'NG')
{
    return (string) PhoneNumber::make($phone, $countryCode);
}


function sendSMS($phone, $message, $sender='NodCredit')
{

    $username = env('ESTORE_SMS_USERNAME');
    $password = env('ESTORE_SMS_PASSWORD');

    $message = urlencode($message);

    $url = sprintf('http://www.estoresms.com/smsapi.php?username=%s&password=%s&sender=%s&recipient=%s&message=%s&dnd=true', $username, $password, $sender, $phone, $message);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    if($err){
        return ['status' => 'error', 'message' => $err, 'data' => null];
    }


    $record = ['phone' => $phone, 'message' => $message, 'response_id' => '', 'response_message' => $response];


    \App\SMSLog::create($record);


    return ['status' => 'ok', 'data' => $response, 'message' => 'ok'];
}


function makeOTP( $len ) {
    $alpha = array();
    for ($u = 1; $u <= 9; $u++) {
        array_push($alpha, $u);
    }
    $rand_alpha_key = array_rand($alpha);
    $rand_alpha = $alpha[$rand_alpha_key];
    $rand = array($rand_alpha);
    for ($c = 0; $c < $len - 1; $c++) {
        array_push($rand, mt_rand(0, 9));
        shuffle($rand);
    }

    return implode('', $rand);
}

function getBVNInfoFromPayStack($bvn)
{
    if(env('APP_ENV') === 'dev')
    {
        //TODO: move to  factory
        $bvnInfo =  new stdClass();
        $bvnInfo->first_name = 'test first name';
        $bvnInfo->last_name = 'test last name';
        $bvnInfo->mobile = generateRandomPhoneNumber();
        $bvnInfo->formatted_dob = '1970-12-12';

        return $bvnInfo;
    }

    $res = makePaystackGetRequest('https://api.paystack.co/bank/resolve_bvn/' . $bvn);

    if ($res['status'] == 'error') {
        return false;
    }

    $respData = $res['data'];

    if ($respData->status) {
        return $respData->data;
    }

    return false;
}


function bladeCompile($value, array $args = array())
{
    $generated = \Blade::compileString($value);
    ob_start() and extract($args, EXTR_SKIP);
    try
    {
        eval('?>'.$generated);
    }
    catch (\Exception $e)
    {
        ob_get_clean(); throw $e;
    }
    $content = ob_get_clean();
    return $content;
}

function cleanAmount($input)
{
    $input = str_ireplace('NGN', '', $input);
    $input = str_replace(',', '', $input);
    $input = preg_replace('/\s+/', ' ',$input);

    return doubleval($input);
}


function runInvestmentLiquidationPayments() {

    $investments = \App\Payment::with('owner')
        ->investments()
        ->notPaid()
        ->liquidatedByUser()
        ->take(10)->get();

    foreach($investments as $investment) {
        $amount = $investment->calculateLiquidatedProfit();
        $owner = $investment->owner;

        if($amount) {

            $createPayload = [
                'type' => 'nuban',
                'name' => $owner->name,
                'description' => 'NodCredit Investment Payment',
                'account_number' => $owner->account_number,
                'bank_code' => $owner->bank->code,
                'currency' => 'NGN',
                'metadata' => ['uinscope' => 'investment', 'stime' => now()]
            ];

            // create transfer receipt
            $createRecipientReq = makePaystackPostRequest('https://api.paystack.co/transferrecipient', $createPayload);

            if($createRecipientReq['status'] === 'ok' && $createRecipientReq['data']->status == 1)
            {
                $recipientCode = $createRecipientReq['data']->data->recipient_code;
                $amount = doubleval($amount * 100);
                $transferPayload = [
                    'source' => 'balance',
                    'reason' => $createPayload['description'],
                    'amount' => $amount,
                    'recipient' =>$recipientCode
                ];

                // Make the transfer
                $transferRequest = makePaystackPostRequest('https://api.paystack.co/transfer', $transferPayload);

                if(!$transferRequest['data']->status) {
                    // it transfer failed
                    \App\NodLog::write($investment->owner, 'NodCredit Investment Payment Failed', 'PayStack Error: ' . e($transferRequest['data']->message) . '. Tried to pay ' . $amount);

                } else {
                    // Transfer was successful
                    $investment->profit_paid_at = now();
                    $investment->save();

                    $admins = \App\User::where('role', 'admin')->get();
                    \Illuminate\Support\Facades\Mail::to($owner)->bcc($admins)->send(new \App\Mail\InvestmentPaid($amount, $investment));

                    \App\NodLog::write($investment->owner, 'NodCredit Investment Payment Success', e($transferRequest['data']->message));
                }
            } else {
                // Unable to create transfer receipt
                $errorMessage = isset($createRecipientReq['data']) ? $createRecipientReq['data']->message : 'Unknown error.';
                \App\NodLog::write($investment->owner, 'NodCredit Investment Payment Failed', 'PayStack Error: Unable to create transfer receipt' . e($errorMessage));
            }
        }
    }
};

function checkLoanRequiredDocumentUpload($hours, $sendRejected=false)
{
    $applications = LoanApplication::checkNewLoansForRequiredDocuments($hours);

    $requiredDocuments = LoanDocumentType::where('is_required', 1)->get()->modelKeys();

    foreach ($applications as $application)
    {
        $documents = \DB::table('loan_documents')
            ->whereRaw('loan_application_id = ?', [$application->id])
            ->get();

        if(count($documents) === count($requiredDocuments))
        {
            // update the application set the required document uploaded to true
            event(new RequiredDocumentUploaded(\App\LoanApplication::find($application->id)));

        }
        else
        {
            // send email to the customer
            event(new RequiredDocumentNotUploaded(\App\LoanApplication::find($application->id), $sendRejected));
        }
    }
}


function getScoreInfo($name, $input=null)
{
    try {
        $scoreParser = new \App\NodCredit\Score\ScoreParser($name, $input);
        return $scoreParser->getInfo();
    } catch (\App\NodCredit\Score\BadScoreConfigException $e)
    {
        return null;
    }
}

function generateRandomPhoneNumber()
{
    $out = '234';
    for ($i = 0; $i<10; $i++)
    {
        $out .= mt_rand(0,9);
    }

    return $out;
}
