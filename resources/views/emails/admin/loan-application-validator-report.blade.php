<p>Hello Admin,</p>
<p>Loan Application from {{ $loanApplication->owner->name }}.</p>
<p>Requested amount: N{{ number_format($loanApplication->amount_requested) }}</p>

@if ($validatorResult->isValid())
    <p style="font-weight: bold;">Allowed amount: N{{ number_format($loanApplication->amount_allowed) }}</p>
@endif


<p style="font-size: 120%; font-weight: bold;">Validator Report</p>

@if ($validatorResult->hasErrors())
    <div style="background: rgba(255,0,0,0.05); padding: 20px;">
        <div style="font-weight: bold; font-size: 110%; margin-bottom: 15px;">ERRORS ({{ count($validatorResult->getErrors()) }})</div>

        @foreach($validatorResult->getErrors() as $key => $error)
            <div style="font-weight: bold; margin-bottom: 5px;">{{ ucfirst(str_replace(['-', '_'], ' ', $key)) }}: <span style="color: red; font-weight: bold;">&times;</span></div>
            <div style="margin-bottom: 20px;">{!! $error !!}</div>
        @endforeach
    </div>
@endif

@if ($validatorResult->hasMessages())
    <div style="background: rgba(0,0,255,0.03); padding: 20px;">
        <div style="font-weight: bold; font-size: 110%; margin-bottom: 15px;">MESSAGES</div>

        @foreach($validatorResult->getMessages() as $key => $message)
            <div style="font-weight: bold; margin-bottom: 5px;">{{ ucfirst(str_replace(['-', '_'], ' ', $key)) }}: <span style="color: green; font-weight: bold;">&#10004;</span></div>
            <div style="margin-bottom: 20px;">{!! $message !!}</div>
        @endforeach
    </div>
@endif

<div style="margin: 20px 0;">

    @if ($validatorResult->isValid())
        @component('emails._partials.btn-success', ['href' => route('mainframe.loans.approval', [
            'id' => $loanApplication->id,
            'loan' => $loanApplication->id,
            'changeStatus' => \App\NodCredit\Loan\Application::STATUS_APPROVED
        ])])
            Change Loan Amount
        @endcomponent
    @else
        @component('emails._partials.btn-success', ['href' => route('mainframe.loans.approval', [
            'id' => $loanApplication->id,
            'loan' => $loanApplication->id,
            'changeStatus' => \App\NodCredit\Loan\Application::STATUS_APPROVED
        ])])
            Approve
        @endcomponent

        @component('emails._partials.btn-primary', ['href' => route('mainframe.loans.show', ['id' => $loanApplication->id])])
            Send New Amount
        @endcomponent
    @endif

    @component('emails._partials.btn-danger', ['href' => route('mainframe.loans.show', [
        'id' => $loanApplication->id,
        'loan' => $loanApplication->id,
        'changeStatus' => \App\NodCredit\Loan\Application::STATUS_REJECTED
    ])])
        Reject
    @endcomponent

</div>

@if ($completedLoanApplications->count())
    <p style="font-size: 120%; font-weight: bold;">Previous Completed loans</p>
    <table style="width: 100%; border-collapse: collapse">
        <thead>
        <tr>
            <th style="padding: 5px; border: 1px solid; text-align: left">Amount</th>
            <th style="padding: 5px; border: 1px solid; text-align: left">Date</th>
            <th style="padding: 5px; border: 1px solid; text-align: left">Tenor</th>
            <th style="padding: 5px; border: 1px solid; text-align: left">Status</th>
            <th style="padding: 5px; border: 1px solid; text-align: left">Type</th>
        </tr>
        </thead>
        <tbody>

        @foreach($completedLoanApplications as $application)
            <tr>
                <td style="padding: 5px; border: 1px solid">{{ $application->amount() }}</td>
                <td style="padding: 5px; border: 1px solid">{{ $application->created_at }}</td>
                <td style="padding: 5px; border: 1px solid">{{ $application->tenor }} months</td>
                <td style="padding: 5px; border: 1px solid">{{ $application->status }}</td>
                <td style="padding: 5px; border: 1px solid">{{ $application->loanType ? $application->loanType->name : '' }}</td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif