<?php

namespace App\Http\Controllers;

use App\Mail\MessageSenderMail;
use App\NodCredit\Account\User as AccountUser;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Collections\InvestmentCollection;
use App\NodCredit\Investment\Exceptions\InvestmentFactoryException;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\Factories\InvestmentFactory;
use App\NodCredit\Investment\InvestmentWithholdingTax;
use App\NodCredit\Investment\Notifications\InvestmentProfitPaymentPaid;
use App\NodCredit\Investment\Notifications\PartialLiquidationPaid;
use App\NodCredit\Investment\PartialLiquidation;
use App\NodCredit\Investment\Payout;
use App\NodCredit\Investment\ProfitPayment;
use App\NodCredit\Message\MessageSender;
use App\NodCredit\Settings;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AdminInvestmentController extends AdminController
{

    public function getIndex()
    {

        if (request()->isXmlHttpRequest()) {

            $investments = InvestmentCollection::findAll();

            return response()->json([
                'investments' => $investments->transform(['user'])
            ]);
        }

        return view('admin.investment.index');
    }

    public function getInvestment(string $id)
    {
        $investment = Investment::find($id);

        return response()->json([
            'investment' => $investment->transform(['user', 'profit_payments', 'partial_liquidations', 'all_logs'])
        ]);
    }

    public function getInvestmentManage(string $id)
    {
        return view('admin.investment.view', [
            'id' => $id
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postInvestmentStartEdit(Request $request, string $id)
    {
        if (! $investment = Investment::find($id)) {
            abort(404);
        }

        $rules = [
            'started_at' => 'date_format:Y-m-d H:i',
            'send_message' => 'boolean',
        ];

        $this->validate($request, $rules);

        try {
            $startDate = Carbon::createFromFormat('Y-m-d H:i', $request->get('started_at'));
            
            $investment->start($startDate, true);
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => [
                    'error' => ['Error. Please, try again or contact administrator.']
                ]
            ], 422);
        }

        if ($investment->isStarted() AND $request->get('send_message') === true) {
            MessageSender::send('investment-started', $investment->getUser(), [
                '#INVESTMENT_AMOUNT#' => Money::formatInNaira($investment->getAmount()),
                '#INVESTMENT_PLAN_NAME#' => $investment->getPlanName(),
                '#INVESTMENT_PLAN_PERCENTAGE#' => $investment->getPlanPercentage(),
            ]);
        }

        $investment->publicLog([
            'text' => 'Investment started. Start date: ' . $startDate->format('Y-m-d H:i'),
            'created_by' => auth()->user()->id,
            'ip' => $request->getClientIp(),
        ]);

        return response()->json();
    }

    /**
     * @param Settings $settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvestmentAdd(Settings $settings)
    {
        $plans = json_decode($settings->get('investmentConfig'));

        $investors = User::where('role', 'partner')->orderBy('name')->get();

        return response()->json([
            'plans' => $plans,
            'min_amount' => $settings->get('investment_min_amount', 10000),
            'max_amount' => $settings->get('investment_max_amount', 1000000),
            'investors' => $investors,
            'profit_payout_types' => Investment::getProfitPayoutTypes()
        ]);
    }

    /**
     * @param Request $request
     * @param AccountUser $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function postInvestmentAdd(Request $request, AccountUser $user)
    {
        $this->validate($request, [
            'user' => 'required|exists:users,id',
            'profit_payout_type' => [
                'required',
                Rule::in(Investment::getProfitPayoutTypeValues())
            ]
        ]);

        try {
            $investment = InvestmentFactory::create(
                $request->get('user'),
                $request->get('amount'),
                $request->get('tenor'),
                $user,
                $request->get('profit_payout_type')
            );
        }
        catch (InvestmentFactoryException $exception) {
            return response()->json([
                'errors' => $exception->getErrors(),
                'message' => $exception->getMessage()
            ], 422);
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => [
                    'error' => ['Error. Please, try again or contact administrator.']
                ]
            ], 422);
        }

        $investment->publicLog([
            'text' => 'Investment created',
            'created_by' => $user->getId(),
            'ip' => $request->getClientIp(),
        ]);

        return response()->json([
            'message' => 'Added'
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postInvestmentEdit(Request $request, string $id)
    {
        $this->validate($request, [
            'plan_name' => 'required',
            'plan_days' => 'required|integer|min:1|max:1095',
            'plan_percentage' => 'required|integer|min:1|max:100',
            'profit_payout_type' => [
                'required',
                Rule::in(Investment::PROFIT_PAYOUT_TYPE_MONTHLY, Investment::PROFIT_PAYOUT_TYPE_SINGLE)
            ],
        ]);

        if (! $investment = Investment::find($id)) {
            abort(404);
        }

        $data = $request->only(['plan_days', 'plan_percentage', 'profit_payout_type', 'plan_name']);

        try {
            $investment->edit($data);
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => [
                    'error' => [$exception->getMessage()]
                ]
            ], 422);
        }

        $investment->hiddenLog([
            'text' => "Investment edited",
            'payload' => $data,
            'created_by' => auth()->user()->id,
            'ip' => $request->getClientIp(),
        ]);

        return response()->json([
            'investment' => $investment->transform()
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postProfitPaymentAutoPayout(Request $request, string $id)
    {
        $this->validate($request, [
            'auto_payout' => 'required|boolean'
        ]);

        $payment = ProfitPayment::find($id);

        try {
            $payment->setAutoPayout($request->get('auto_payout'));
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => [
                    'auto_payout' => [$exception->getMessage()]
                ]
            ], 422);
        }

        return response()->json([
            'message' => 'Auto-payout changed to ' . ($payment->getAutoPayout() ? 'ON' : 'OFF')
        ]);
    }

    public function getPartialLiquidationPayout(string $id, AccountUser $accountUser, Request $request)
    {
        $partialLiquidation = PartialLiquidation::find($id);

        if (! $partialLiquidation) {
            abort(404);
        }

        try {
            Payout::partialPayout($partialLiquidation);
        }
        catch (\Exception $exception) {
            Log::channel('investments-payout-partial-liquidations')->info("[{$partialLiquidation->getId()}] Manual Payout {$partialLiquidation->getAmount()}: failed. Exception message: {$exception->getMessage()}");

            return response()->json([
                'message' => "Payout failed. Error message: {$exception->getMessage()}"
            ], 422);
        }

        // Success
        Log::channel('investments-payout-partial-liquidations')->info("[{$partialLiquidation->getId()}] Manual Payout {$partialLiquidation->getAmount()}: successful.");

        PartialLiquidationPaid::notify($partialLiquidation, 'user');

        $partialLiquidation->getInvestment()->publicLog([
            'text' => "Transfer of partial liquidation (" . Money::formatInNaira($partialLiquidation->getAmount()) . ") to official bank account",
            'created_by' => $accountUser->getId(),
            'ip' => $request->getClientIp()
        ]);

        return response()->json([
            'message' => 'OK'
        ]);
    }

    /**
     * @param string $id
     * @param AccountUser $accountUser
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfitPaymentPayout(string $id, AccountUser $accountUser, Request $request)
    {
        $payment = ProfitPayment::find($id);

        if (! $payment) {
            abort(404);
        }

        try {
            Payout::profitPayout($payment);
        }
        catch (\Exception $exception) {

            Log::channel('investments-payout-profit-payments')->info("[{$payment->getId()}] Manual Payout {$payment->getAmount()}: failed. Exception message: {$exception->getMessage()}");;

            return response()->json([
                'message' => "Payout failed. Error message: {$exception->getMessage()}"
            ], 422);
        }

        // Success
        Log::channel('investments-payout-profit-payments')->info("[{$payment->getId()}] Manual Payout {$payment->getAmount()}: successful.");

        $payment->getInvestment()->publicLog([
            'text' => "Transfer of interest (" . Money::formatInNaira($payment->getAmount()) . ") to official bank account",
            'created_by' => $accountUser->getId(),
            'ip' => $request->getClientIp()
        ]);

        InvestmentProfitPaymentPaid::notify($payment);

        return response()->json([
            'message' => 'OK'
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param AccountUser $accountUser
     * @return \Illuminate\Http\JsonResponse
     */
    public function postInvestmentWithholdingTaxEdit(Request $request, string $id, AccountUser $accountUser)
    {
        $this->validate($request, [
            'withholding_tax_percent' => 'required|integer|min:0|max:100'
        ]);

        if (! $investment = Investment::find($id)) {
            abort(404);
        }

        $oldValue = $investment->getWithholdingTaxPercent();
        $newValue = (int) $request->get('withholding_tax_percent');

        if ($oldValue !== $newValue) {

            try {
                InvestmentWithholdingTax::edit($investment, $newValue);
            }
            catch (\Exception $exception) {
                return response()->json([
                    'errors' => [
                        'error' => ['Something wrong. Please, try again later or contact admin.']
                    ]
                ], 422);
            }

            $investment->hiddenLog([
                'text' => "Withholding tax changed from {$oldValue}% to {$newValue}%",
                'created_by' => $accountUser->getId(),
                'ip' => $request->getClientIp()
            ]);
        }

        return response()->json([
            'investment' => $investment->transform()
        ]);
    }
}
