@extends('emails.layouts.struct')

@section('content')
    <p>Hello {{ $user->getName() }},</p>
    <p>We have made a transfer of NGN{{ number_format($investment->getPayoutAmount() ,2) }} to your official bank account for the investment you just liquidated with us.</p>
    <p>For more information <a href="{{route('login')}}">click here</a> to log into your account.</p>
    <p>- NodCredit Team</p>
@endsection