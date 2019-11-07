<p>Hello {{ $application->getUser()->name }},</p>
<p>From our review of your Bank Statement, you're only able to apply for a maximum of <b>N{{ number_format($application->getModel()->amount_allowed) }}</b>.</p>
<p>Should we continue with your Loan approval?</p>

<div style="margin: 20px 0;">

    @component('emails._partials.btn-success', ['href' => route('account.loans.amount-confirm-manually', ['id' => $application->getId()])])
        Yes
    @endcomponent

    @component('emails._partials.btn-danger', ['href' => route('account.loans.amount-reject', ['id' => $application->getId()])])
        No
    @endcomponent

</div>

<p>- NodCredit Team</p>
