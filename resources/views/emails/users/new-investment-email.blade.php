@extends('emails.layouts.struct')

@section('content')
<p>
    Hello {{$user->name}},
</p>

<p>
    You have successfully added an investment with NodCredit - N{{number_format($investment->amount)}}
</p>

<p>
    Your investment is been reviewed and will start shortly.
</p>


<p>
    For more information <a href="{{route('login')}}">click here</a> to log into your account.
</p>


<p>
    - NodCredit Team
</p>

@endsection