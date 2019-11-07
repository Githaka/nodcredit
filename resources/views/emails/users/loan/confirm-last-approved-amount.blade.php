<p>Hello {{ $application->getUser()->name }},</p>
<p>From our review of your Bank Statement, we can only approve your previous completed loan amount - <b>N{{ number_format($application->getAmountAllowed()) }}</b>.</p>
<p>Should we continue with your Loan approval?</p>

<div style="margin: 20px 0;">

    @component('emails._partials.btn-success', ['href' => route('account.loans.amount-confirm', ['id' => $application->getId()])])
        Yes
    @endcomponent

    @component('emails._partials.btn-danger', ['href' => route('account.loans.amount-reject', ['id' => $application->getId()])])
        No
    @endcomponent

</div>


<p>- NodCredit Team</p>
