<p>Hello {{$application->owner->name}}</p>

@if ($sendRejected)
    <p>Your loan application has been rejected</p>
    <p>Reason: <strong>Requirement document was not provided with in the required time-frame.</strong></p>
@else
    <p>You recently applied for a loan but yet to provide us with a valid work ID and your bank statement (previous 1 to 3 months) as part of the loan assessment.</p>
    <p>If you have provided the following documents, then kindly ignore this mail, else your loan application will be rejected in 24 hours.</p>
@endif

<p>Regards<br />
    NodCredit.</p>
