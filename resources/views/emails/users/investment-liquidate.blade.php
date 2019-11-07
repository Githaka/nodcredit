<p>
    Hello {{$user->name}},
</p>

<p>We received your liquidate request with this note.</p>
<p>
  <strong>{{$investment->investment_liquidation_reason}}</strong>
</p>

<p>
    <strong>PS: Your liquidation is in process and your account will be credited in 24 hours.</strong>
</p>


<p>
    For more information <a href="{{route('login')}}">click here</a> to log into your account.
</p>


<p>
    - NodCredit Team
</p>
