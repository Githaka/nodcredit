<p>
    Hello {{$user->name}},
</p>

<p>New update on your loan application. The status has changed to <strong>{{strtolower($loan->status)}}</strong></p>

@if($loan->status === 'approved')
    <h2>See your re-payment plan below.</h2>
    <table border="1" cellpadding="1" cellspacing="1">
        <thead>
        <tr>
            <td>Amount</td>
            <td>Due Date</td>
            <td>Month</td>
        </tr>
        </thead>
        <tbody>
        @foreach($loan->payments as $payment)
            <tr>
                <td>NGN{{number_format($payment->amount,2)}}</td>
                <td>{{$payment->due_at}}</td>
                <td>{{$payment->payment_month}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<p>
    For more information <a href="{{route('login')}}">click here</a> to log into your account.
</p>



<p>
    - NodCredit Team
</p>
