<p>
    Hello {{$user->name}},
</p>

<p>Your investment has started.</p>
<p>
    You choose tenor of <strong>{{$investment->investment_tenor}}</strong> <br />
    Investment will mature at <strong>{{$investment->investment_ended}}</strong>
</p>

<p>
   <strong>PS: You can liquidate your investment anytime.</strong>
</p>


<p>
    For more information <a href="{{route('login')}}">click here</a> to log into your account.
</p>


<p>
    - NodCredit Team
</p>
