<?php

namespace App\Http\Controllers;

use App\Mail\MessageSenderMail;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Notifications\InvestmentLiquidationRequest;
use App\NodCredit\Message\MessageSender;
use App\User as UserModel;
use App\Mail\Investment\LiquidationMail;
use App\NodCredit\Account\User;
use App\NodCredit\Investment\Collections\InvestmentCollection;
use App\NodCredit\Investment\Exceptions\InvestmentLiquidationException;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\InvestmentLiquidation;
use App\NodCredit\Investment\Models\InvestmentModel;
use App\NodCredit\Investment\PartialLiquidation;
use App\NodCredit\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AccountInvestmentController extends Controller
{


    public function getInvestments(Settings $settings)
    {
        $investments = InvestmentCollection::findByUserId(auth()->user()->id);

        return response()->json([
            'investments' => $investments->transform(),
            'investments_count' => $investments->count(),
            'investments_amount' => $investments->sumAmount(true),
            'liquidation_penalty' => $settings->get('investment_liquidation_penalty')
        ]);
    }

    public function getInvestment(string $id, User $accountUser, Settings $settings)
    {

        $model = InvestmentModel::where('id', $id)->where('user_id', $accountUser->getId())->first();

        if (! $model) {
            abort(404);
        }

        $investment = new Investment($model);

        return response()->json([
            'investment' => $investment->transform(),
            'liquidation_penalty' => $settings->get('investment_liquidation_penalty')
        ]);
    }

    public function postInvestmentLiquidate(Request $request, string $id, User $accountUser)
    {
        $model = InvestmentModel::where('id', $id)->where('user_id', $accountUser->getId())->first();

        if (! $model) {
            abort(404);
        }

        $amount = floatval($request->get('amount', 0));
        $reason = $request->get('reason', '');

        $investment = new Investment($model);

        try {
            $success = InvestmentLiquidation::liquidate($investment, $amount, $reason, $accountUser);
        }
        catch (InvestmentLiquidationException $exception) {
            return response()->json([
                'errors' => $exception->getErrors(),
                'message' => $exception->getMessage()
            ], 422);
        }

        if ($success) {

            $investment->publicLog([
                'text' => "Liquidate request of " . Money::formatInNaira($amount) . ". Reason: {$reason}",
                'created_by' => $accountUser->getId(),
                'ip' => $request->getClientIp(),
            ]);

            InvestmentLiquidationRequest::notify($investment, 'all', $amount, $reason);
        }

        return response()->json([
            'success' => true
        ]);
    }

}
