<?php

namespace App\Http\Controllers\API;

use App\Events\UserUpdatedProfile;
use App\Http\Requests\API\AccountUpdateRequest;
use App\NodCredit\Account\User;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Loan\Application;
use App\NodCredit\Account\Exceptions\DeviceLinkException;
use Illuminate\Http\Request;

class AccountController extends ApiController
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccount()
    {
        return $this->successResponseWithUser('OK');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard()
    {

        $totalReceived = $this->user()
            ->applications()
            ->whereIn('status', [Application::STATUS_COMPLETED, Application::STATUS_APPROVED])
            ->sum('amount_approved');

        $totalPaid = 0;

        $applications = $this->user()->applications()->get();

        foreach ($applications as $application) {

            $payments = $application->payments()->where('status', 'paid')->get();

            foreach ($payments as $payment) {
                $totalPaid += $payment->amount;
            }
        }

        $data = [
            'total_received' => Money::formatInNairaAsArray($totalReceived),
            'total_paid' => Money::formatInNairaAsArray($totalPaid),
            'payments' => []
        ];

        $loan = $this->user()
            ->applications()
            ->where('status', Application::STATUS_APPROVED)
            ->whereHas('payments', function($query) {
                $query->where('status', 'scheduled');
            })
            ->first();

        if ($loan) {
            $application = new Application($loan);
            $payments = $application->getScheduledPayments();

            if ($payments->count()) {
                $data['payments'] = $payments->transform();
            }
        }

        return $this->successResponseWithUser('OK', $data);
    }

    /**
     * @param AccountUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAccount(AccountUpdateRequest $request)
    {
        $user = $this->user();

        if ($request->get('password')) {
            $user->password = $request->get('password');
        }

        $user->save();

        event(new UserUpdatedProfile($user));

        return $this->successResponseWithUser();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChecklist()
    {
        $result = [];

        $checklistOutput = $this->user()->checkList();

        foreach ($checklistOutput as $key => $section) {
            foreach (array_get($section, 'items') as $item) {
                $result[$key][] = $item;
            }
        }

        return $this->successResponse('OK', [
            'checklist' => $result
        ]);
    }

    /**
     * @param Request $request
     * @param User $accountUser
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkDevice(Request $request, User $accountUser)
    {
        if (! $deviceId = $request->get('deviceId')) {
            return $this->errorResponse('Device ID is required');
        }

        try {
            $accountUser->linkDeviceId($deviceId);
        }
        catch (DeviceLinkException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse();
    }

}