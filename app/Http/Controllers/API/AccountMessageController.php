<?php

namespace App\Http\Controllers\API;

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

class AccountMessageController extends ApiController
{

    public function getMessages(Request $request)
    {
        $models = Message::where('user_id', $this->user()->id)
            ->orderBy('created_at', 'DESC')
            ->paginate();

        return $this->successResponse('OK', [
            'total' => $models->total(),
            'perPage' => $models->perPage(),
            'currentPage' => $models->currentPage(),
            'items' => $models->items()
        ]);
    }

    public function getMessageContent(string $id)
    {
        $message = Message::where('id', $id)->where('user_id', $this->user()->id)->first();

        if (! $message) {
            abort(404);
        }

        if (! preg_match('/\<\w{1,6}\>/i', $message->message)) {
            $content = nl2br($message->message);
        }
        else {
            $content = $message->message;
        }

        return response($content);
    }

}