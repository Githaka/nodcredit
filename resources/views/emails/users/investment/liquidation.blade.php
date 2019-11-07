@extends('emails.layouts.struct')

@section('content')
    <p>Hello {{ $user->getName() }},</p>
    <p>We received your liquidate request with this note.</p>
    <p><strong>{{ $reason }}</strong></p>
    <p><strong>PS: Your liquidation is in process and your account will be credited in 24 hours.</strong></p>
    <p>For more information <a href="{{route('login')}}">click here</a> to log into your account.</p>
    <p>- NodCredit Team</p>
@endsection