<p>
    Hello {{$payment->loan->owner->name}},
</p>

<p>We have received your payment of NGN{{number_format($payment->amount,2)}}. This payment was made for the {{$payment->monthInfo()}} of your loan re-payment.</p>

<p>Thank you for keeping up with your re-payment plan.</p>

<p>
    For more information <a href="{{route('login')}}">click here</a> to log into your account.
</p>


<p>
    - NodCredit Team
</p>
