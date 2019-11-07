<?php

namespace App\Http\Controllers\API;

use App\Events\UserUpdatedProfile;
use App\Http\Requests\API\AccountCardAddRequest;
use App\Http\Requests\API\AccountUpdateRequest;
use App\Message;
use App\NodCredit\Account\Transformers\UserCardTransformer;
use App\NodCredit\Account\User;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Loan\Application;
use App\NodCredit\Message\Collections\MessageCollection;
use App\NodLog;
use App\Paystack\PaystackApi;
use App\UserCard;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class AccountCardController extends ApiController
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCards()
    {
        $cards = $this->user()->cards()->get();

        $cards = $cards->map(function(UserCard $card) {
            return UserCardTransformer::transform($card);
        });

        return $this->successResponseWithUser('OK', [
            'cards' => $cards
        ]);
    }

    public function addCard(AccountCardAddRequest $request, PaystackApi $paystackApi)
    {
        $amount = 20;

        $data = [
            'amount' => $amount * 100,
            'email' => $this->user()->email,
            'callback_url' => route('paystack.card.add-callback'),
        ];

        try {
            $response = $paystackApi->initTransaction($data);
        }
        catch (ClientException $exception) {
            $json = json_decode($exception->getResponse()->getBody()->getContents());

            return $this->errorResponse($json->message);
        }
        catch (\Exception $exception) {
            return $this->errorResponse('Error. Try again later.');
        }

        if (! $response->status) {
            return $this->errorResponse($response->message);
        }

        $paymentData = [
            'amount' => $amount,
            'reason' => 'card-link',
            'payment_reference' => $response->data->reference
        ];

        $this->user()->payments()->create($paymentData);

        return $this->successResponse('OK', [
            'authorization_url' => $response->data->authorization_url
        ]);
    }

    /**
     * @param User $accountUser
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCard(User $accountUser, string $id)
    {

        // Defaulter can`t remove a card
        if ($accountUser->isDefaulter()) {
            return $this->errorResponse('You can not remove a card.');
        }

        $card = UserCard::where('id', $id)->where('user_id', $accountUser->getId())->first();

        if (! $card) {
            return $this->errorResponse('Card not found.');
        }

        if ($accountUser->getCards()->count() < 2) {
            return $this->errorResponse('You can not remove this card. Add a new one and try again.');
        }

        NodLog::write(auth()->user(), 'Card deleted', json_encode($card->toArray()));

        $card->delete();

        auth()->user()->getScoreInfo('USER_REMOVED_CARD', auth()->user()->cards()->count());

        event(new UserUpdatedProfile($accountUser->getModel()));

        return $this->successResponse('Deleted');
    }

}