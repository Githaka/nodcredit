<?php

namespace App\Http\Controllers\API;




use App\Events\UserUpdatedProfile;
use App\NodCredit\Account\User;
use App\Payment;
use App\Paystack\PaystackApi;
use App\UserCard;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class PaystackController extends ApiController
{

    public function addCardCallback(Request $request, PaystackApi $paystackApi)
    {

        $payment = Payment::where('status', 'pending')
            ->where('payment_reference', $request->get('trxref'))
            ->first();

        if (! $payment) {
            abort(404);
        }

        $user = $payment->owner;
        $accountUser = new User($user);

        try {
            $apiResponse = $paystackApi->verifyTransaction($payment->payment_reference);
        }
        catch (ClientException $exception) {
            $json = \GuzzleHttp\json_decode($exception->getResponse()->getBody()->getContents());

            return response($json->message);
        }
        catch (\Exception $exception) {
            return response('Payment error. Try again or contact admin.');
        }

        if ($apiResponse->data->status !== 'success') {
            $payment->status = 'rejected';
            $payment->save();

            return response($apiResponse->data->gateway_response);
        }

        if ($apiResponse->data->amount < $payment->amount) {
            $payment->status = 'rejected';
            $payment->reason = $apiResponse->data->gateway_response;
            $payment->save();

            return response('The amount you paid is less than the transaction amount. PAID: N' . number_format($apiResponse->data->amount / 100));
        }

        // Success payment. Link card
        $payment->status = 'success';
        $payment->save();

        $cardNumber = sprintf('%s***%s', $apiResponse->data->authorization->bin, $apiResponse->data->authorization->last4);

        $cardData  = [
            'currency' => $apiResponse->data->currency,
            'auth_code' => $apiResponse->data->authorization->authorization_code,
            'card_number' => $cardNumber,
            'exp_month' => $apiResponse->data->authorization->exp_month,
            'exp_year' => $apiResponse->data->authorization->exp_year,
            'card_type' => $apiResponse->data->authorization->card_type,
            'brand' => $apiResponse->data->authorization->brand,
            'reusable' => $apiResponse->data->authorization->reusable,
            'signature' => $apiResponse->data->authorization->signature,
            'email' => $apiResponse->data->customer->email,
            'bin' => $apiResponse->data->authorization->bin,
            'last4' => $apiResponse->data->authorization->last4,
            'bank_name' => strtoupper($apiResponse->data->authorization->bank),
        ];

        // Check exists card owner
        $existsCard = UserCard::where('bin', $apiResponse->data->authorization->bin)
            ->where('last4', $apiResponse->data->authorization->last4)
            ->where('exp_month', $apiResponse->data->authorization->exp_month)
            ->where('exp_year', $apiResponse->data->authorization->exp_year)
            ->where('signature', $apiResponse->data->authorization->signature)
            ->withTrashed()
            ->first();

        // If card exists and card owner is another user
        if ($existsCard AND $existsCard->user_id !== $accountUser->getId()) {

            // User who has active loan should not be banned
            if (! $accountUser->hasActiveLoan()) {

                $cardOwner = $existsCard->user;

                // Investor should not be banned if card added exists as user
                if (! $accountUser->getModel()->isPartner() OR $cardOwner->isUser()) {

                    $accountUser->ban("Adding card data [{$existsCard->id}] which were added by {$cardOwner->name} ({$cardOwner->email}).");

                    throw new \Exception('You can not use this card. Your account is suspended. Please, contact admin.');
                }
            }
        }

        /** @var UserCard $card */
        $card = $user->cards()
            ->where('bin', $apiResponse->data->authorization->bin)
            ->where('last4', $apiResponse->data->authorization->last4)
            ->where('exp_month', $apiResponse->data->authorization->exp_month)
            ->where('exp_year', $apiResponse->data->authorization->exp_year)
            ->where('signature', $apiResponse->data->authorization->signature)
            ->first();

        if (! $card) {
            $card = $user->cards()->create($cardData);

            if ($user->cards()->count()) {
                // if this is an additional card, reward the user
                $user->getScoreInfo('ADDING_ADDITIONAL_CARD');
            }

        }
        else {
            // Reset disable status
            $cardData['disabled_at'] = null;
            $cardData['disable_message'] = null;

            $card->update($cardData);
        }

        // If user has no a valid card, verify just added card and show error message
        if (! $accountUser->hasValidCard()) {

            // Verify card issuer and user bank
            if (! $card->isMatchingBankName()) {
                return response('This card does not match your banking institution. Please, add another card.');
            }

            // Verify card expire date
            if (! $card->isValidExpireAt()) {
                return response('This card expires in less than 3 months. Please, add a new card.');
            }

            // Verify if card is reusable
            if (! $card->isReusable()) {
                return response('This card is not a valid. Please, add a new card.');
            }
        }

        event(new UserUpdatedProfile($accountUser->getModel()));

        return response('Card was successfully added.');
    }


}