<p>Hello {{ $application->getUser()->name }},</p>

<p>You applied for a loan {{ $application->getCreatedAt()->diffInDays() }} days ago,</p>
<p>To confirm you're still interested in the loan, click YES, and we will go ahead to process your documents.</p>

<div style="margin: 20px 0;">

    @component('emails._partials.btn-success', ['href' => route('loan.handling-confirmation.confirm', ['token' => $application->getHandlingConfirmationToken()])])
        YES
    @endcomponent

    @component('emails._partials.btn-danger', ['href' => route('loan.handling-confirmation.reject', ['token' => $application->getHandlingConfirmationToken()])])
        NO
    @endcomponent

</div>

<p>- NodCredit Team</p>
