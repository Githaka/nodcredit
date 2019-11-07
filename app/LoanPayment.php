<?php

namespace App;
use App\Events\SendMessage;

class LoanPayment extends BaseModel
{
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SCHEDULED = 'scheduled';

    protected $fillable =[
        'due_at',
        'amount',
        'loan_application_id',
        'status',
        'payment_info',
        'payment_month',
        'interest',
        'original_amount',
        'need_to_charge_for_pause',
        'penalty_paused_until',
        'penalty_paused_by',
    ];

    protected $casts = [
        'need_to_charge_for_pause' => 'boolean'
    ];

    protected $dates = [
        'due_at',
        'created_at',
        'updated_at',
        'penalty_paused_until'
    ];

    public function dueDate()
    {
        return $this->due_at ? $this->due_at->toFormattedDateString() : 'Null';
    }
    public function loan()
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function isOverdue()
    {
        return $this->due_at && $this->due_at->isPast();
    }

    public function failedLog()
    {
        return $this->hasMany(FailedBilling::class);
    }

    public function transactionLogs()
    {
        return $this->hasMany(TransactionLog::class, 'model_id')->where('model', 'LoanPayment');
    }

    /**
     * Payment payments
     */
    public function parts()
    {
        return $this->hasMany(PartPayment::class);
    }

    public function penaltyPausedBy()
    {
        return $this->belongsTo(User::class, 'penalty_paused_by');
    }

    public function monthInfo()
    {
        if($this->payment_month == 1) return '1st month';
        if($this->payment_month == '2') return '2nd month';
        if($this->payment_month == '3') return '3rd month';
        return $this->payment_month . 'th month';
    }

    public function getAmount()
    {
        return 'NGN ' . number_format($this->amount);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function getBadgeCode()
    {
        if($this->status == 'scheduled') return 'info';
        if($this->status == 'paid') return 'success';
        if($this->status == 'processing') return 'warning';
        return 'danger';
    }


    /**
     * Fetch payments that are due in days
     * This method fetch the payment from the start of that day to the end of that day
     * @param int $days
     * @param int $limit
     * @param int $offset
     *
     * @return mixed
     */
    public static function duePaymentsInDays($days=1, $limit=50, $offset=0)
    {

        $startOfTheDay = now()->addDays($days)->startOfDay();
        $endOfTheDay = now()->addDays($days)->endOfDay();
        return static::where('status', 'scheduled')
                                ->whereBetween('due_at', [$startOfTheDay, $endOfTheDay])
                                ->whereHas('loan', function($q){
                                    $q->where('status', 'approved')
                                    ->whereNotNull('paid_out');
                                })
                                ->with('loan.owner')
                                ->offset($offset)
                                ->limit($limit)
                                ->get();
    }

    public static function chargeWithPayStack(LoanPayment $payment)
    {

        $owner = $payment->loan->owner;
        $loan = $payment->loan;

        $chargeAmount = doubleval($payment->amount) * 100;
        $fields = [
            'email' => $loan->owner->email,
            'amount' => $chargeAmount,
            'authorization_code' => $loan->owner->card->auth_code,
            'metadata' => ['custom_fields' => ['payment_for' => 'loan_repayment', 'payment_id' => $payment->id]]
        ];

        $res = makePaystackPostRequest('https://api.paystack.co/transaction/charge_authorization', $fields);

        if($res['status'] !== 'ok')
        {
            BillingLog::create(['loan_payment_id' => $payment->id, 'info' => $res['message']]);
            return $res['message'];
        }

        $data = $res['data']->data;
        if($data->status == 'success')
        {

            $payment->status = 'paid';
            $payment->save();
            $payment->payload = serialize($data);

            // check if loan is completed
            $loan->checkForCompletedLoanRepayment();

            TransactionLog::create([
                'trans_type' => 'debit',
                'payload' => $payment->payload,
                'amount' => $payment->amount,
                'performed_by' => auth()->user()->id,
                'user_id' => $payment->loan->owner->id,
                'card_id' => $loan->owner->card->id,
                'status' => 'successful',
                'model' => 'LoanPayment',
                'model_id' => $payment->id,
                'response_message' => 'successful',
                'pay_for' => 'Loan repayment'
            ]);

            $message = Message::create([
                'message' => 'Your loan-repayment was successful. Amount billed: N' . number_format($payment->amount,2),
                'message_type' => 'email',
                'subject' => 'Loan Re-payment Alert',
                'user_id' => $owner->id,
                'sender' => $owner->id
            ]);

            event(new SendMessage($message));

            return 'ok'; // this is intentional
        }
        else
        {
            BillingLog::create(['loan_payment_id' => $payment->id, 'info' => $data->gateway_response]);
            return $data->gateway_response;
        }
    }

    public static function generateDefaultCSV($filename = 'records.csv')
    {
        $limit = 100;
        $offset  = 0;
        $rows  = ["RECIPIENT NAME,PHONE,EMAIL,LOAN AMOUNT,DISBURSEMENT DATE,INTEREST DUE, INTEREST DUE DATE,PAID BACK,RECIPIENT A/C NO,BANK ACCOUNT"];
        while(true)
        {
            $records = static::where('status', static::STATUS_SCHEDULED)
                ->where('due_at', '<', now())
                ->whereHas('loan', function($q)
                {
                    $q->where('status', static::STATUS_APPROVED)
                        ->whereNotNull('paid_out');
                })
                ->orderBy('due_at', 'DESC')
                ->offset($offset)
                ->limit($limit)->get();

            $total = count($records);
            if(!$total) break;

            foreach($records as $record)
            {

                $paidBack = $record->parts()->sum('amount');

                $rows[] = sprintf("%s,%s,%s,%s,%s,%s,%s,%s,'%s',%s",
                    str_replace(',', '', $record->loan->owner->name),
                    $record->loan->owner->phone,
                    $record->loan->owner->email,
                    $record->loan->amount_approved,
                    date('d/m/Y H:i:s', strtotime($record->loan->paid_out)),
                    $record->amount,
                    date('d/m/Y H:i:s', strtotime($record->due_at)),
                    $paidBack,
                    $record->loan->owner->account_number,
                    $record->loan->owner->bank->name
                );
            }

            $offset += $limit;
        }


        $filename = storage_path($filename);

        $bytesWritten = file_put_contents($filename, implode(PHP_EOL, $rows));

        if($bytesWritten)
        {
            return $filename;
        }

        return false;
    }

    public function isDefault(): bool
    {
        if ($this->status === \App\NodCredit\Loan\Payment::STATUS_PAID) {
            return false;
        }

        if ($this->due_count < 2) {
            return false;
        }

        if ($this->due_at AND $this->due_at->isFuture()) {
            return false;
        }

        return true;
    }

    public function isPenaltyPaused(): bool
    {

        if ($this->penalty_paused_until AND $this->penalty_paused_until->isFuture()) {
            return true;
        }

        return false;
    }
}
