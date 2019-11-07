<p>Hello {{ $user->name }},</p>
<p>Your uploaded Bank Statement period is not valid. {{ $reason }}</p>
<p>Please, upload valid bank statement.</p>
<div style="margin: 20px 0; ">
    @component('emails._partials.btn-primary', ['href' => route('account.loans.show', ['id' => $application->getId()])])
        Go to Loan Application
    @endcomponent
</div>
<p>- NodCredit Team</p>
