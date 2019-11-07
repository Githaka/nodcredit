Hello {{$user->name}},

Congratulations!! We just made a transfer of NGN{{number_format($loan->amount_approved,2)}} to your account.

Find below your re-payment plan
<hr>

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


Log into your account for more details.

- NodCredit



