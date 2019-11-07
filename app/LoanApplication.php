<?php

namespace App;
use App\Events\SendMessage;
use Carbon\Carbon;


class LoanApplication extends BaseModel
{
    protected $fillable = [
        'user_id',
        'amount_requested',
        'amount_approved',
        'loan_type_id',
        'payment_month',
        'tenor',
        'interest_rate',
        'status'
    ];

    protected $hidden = ['loan_type_id', 'deleted_at', 'user_id', 'updated_at'];

    protected $casts = [
        'user_pause_count' => 'integer'
    ];

    protected $dates = [
        'user_paused_at'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
        //->select('id', 'email', 'name', 'phone', 'bvn', 'avatar_url', 'bank')
        //->select('id', 'email', 'name', 'phone', 'bvn', 'avatar_url', 'bank')
    }

    public function loanType()
    {
        return $this->belongsTo(LoanType::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getAmountRequestedAttribute($value)
    {
        if(doubleval($this->amount_approved) > 0) {
            return $this->amount_approved;
        }

        return doubleval($value);
    }



    public function documents()
    {
        return $this->hasMany(LoanDocument::class);
    }

    public function amount()
    {
        return 'NGN'.number_format($this->amount_requested, 2);
    }

    public function documentType()
    {
        return $this->belongsTo(LoanDocumentType::class, 'id', 'document_type');
    }

    public function getUploadedDocumentTypeInfo()
    {
        $documents = $this->documents;
        $uploaded = count($documents);
        $required = LoanDocumentType::where('is_required', true)->count();
        $uploadedRequired = 0;
        $uploadedIds = [];
        $requiredNames = [];

        foreach(LoanDocumentType::get() as $item)
        {
            if($item->is_required)
            {
                $requiredNames[]  = $item->name;
            }
            foreach($documents as $document)
            {
                if($item->is_required  && $item->id == $document->document_type){
                    $uploadedRequired++;
                }
            }
        }

        foreach($documents as $document)
        {
            $uploadedIds[] = $document->document_type;
        }

        return [
            'uploaded' => $uploaded,
            'required' => $required,
            'uploadedRequired' => $uploadedRequired,
            'uploadedIds' => $uploadedIds,
            'requiredNames' => $requiredNames
        ];
    }

    public function calculateMonthlyPayments()
    {
        $p = doubleval($this->amount_requested);
        $monthlyChunk = 1+($p / $this->tenor);
        $balance = $p;
        $payments = [];
        $i=1;
        while($i <= $this->tenor)
        {
            $payments[] = percentOf($balance, $this->interest_rate) + $monthlyChunk;
            $balance -= $monthlyChunk;
            $i++;
        }

        return $payments;
    }


    public static function buildMonthlyPayment($amount, $interestRate,  $duration) {


        $p = doubleval($amount);
        $monthlyChunk = ($p /$duration);
        $balance = $p;
        $payments = [];
        $i=1;
        while($i <= $duration)
        {

            $interest = percentOf($balance, $interestRate);
            $value =  round($interest + $monthlyChunk, 2);
            $payments[] = ['amount' => $value, 'month' => $i, 'interest' => $interest];
            $balance -= $monthlyChunk;
            $i++;
        }

        return $payments;

    }

    public function createPaymentEntry()
    {

        $this->payments()->delete();
        $payments = LoanApplication::buildMonthlyPayment($this->amount_approved, $this->interest_rate,  $this->tenor);
        $ids = [];
        foreach($payments as $payment)
        {
            $ids[] = $this->payments()->create([
                            'amount' => $payment['amount'],
                            'due_at' => now()->addMonths($payment['month'])->endOfDay(),
                            'payment_month' => $payment['month'],
                            'interest' => $payment['interest']
                        ]);
        }

        return $ids;
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class, 'loan_application_id');
    }

    public function totalExpectedPayback()
    {
        return $this->payments()->sum('amount');
    }

    public static function createDraftedLoan(User $user) {

    }

    public static function getTotalPendingPayment($loanId) {
        $total = 0;
        $loan = static::find($loanId);
        if($loan)
        {
            $payments = $loan->payments()->where('status', 'scheduled')->get();
            foreach($payments as $payment) {
                $total += $payment->amount;
            }
        }
        return $total;
    }

    public function loanActions()
    {
        return $this->hasMany(LoanAction::class);
    }

    public function checkForCompletedLoanRepayment()
    {
        if($this->payments()->where('status', 'scheduled')->count() == 0)
        {
            $this->status = 'completed';
            $this->save();
            $message = Message::create([
                'message' => 'You have successful completed your loan re-payment.',
                'message_type' => 'email',
                'subject' => 'Loan Successfully Paid',
                'user_id' => $this->owner->id,
                'sender' => $this->owner->id
            ]);
            event(new SendMessage($message));
        }
    }

    /**
     * Get loans that are new and have not uploaded required documents within $hours
     */
    public static function checkNewLoansForRequiredDocuments($hours)
    {

        $dateNow = now()->toDateTimeString();

        return \DB::table('loan_applications')
                       ->selectRaw("*, hour(timediff(?, created_at)) as h", [$dateNow])
                            ->whereRaw('hour(timediff(?, created_at)) = ?', [$dateNow, $hours])
                            ->whereNull('required_documents_uploaded')
                            ->where('status', 'new')
                        ->get();
    }

    /**
     * Check if a loan application has all the required documents uploaded
     * @return bool
     */
    public function hasAllRequiredDocuments()
    {
        $requiredDocuments = LoanDocumentType::where('is_required', 1)->get()->modelKeys();

        $documents = $this->documents->pluck('document_type')->toArray();


        $uploaded = true;

       foreach($requiredDocuments as $doc)
       {
           if(!in_array($doc, $documents))
           {
               $uploaded = false;
           }
       }

       return $uploaded;
    }

    /**
     * Check if loan application has all required documents IN realtime.
     * @return bool
     */
    public function hasRequiredDocuments(): bool
    {
        $requiredIds = LoanDocumentType::where('is_required', 1)->select('id')->get()->pluck('id')->toArray();
        $requiredIds = array_unique($requiredIds);

        $uploadedIds = $this->documents()->select('document_type')->get()->pluck('document_type')->toArray();
        $uploadedIds = array_unique($uploadedIds);

        $diff = array_diff($requiredIds, $uploadedIds);

        if (count($diff)) {
            return false;
        }

       return true;
    }
}
