@extends('layouts.front')

@section('content')
    <body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top" data-offset="100" class="with-side-menu">

    @include('includes.flash-messages')
    <div class="rk-autobox">
        <div class="container">
            <div class="text-center"><a href="{{url('/')}}"><img src="{{asset('static/images/logo2.png')}}" class="img-responsive login-logo" alt="Logo"></a></div>
            @if($user)
                <div class="alert alert-success alert-fill alert-close alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    Email address verified
                </div>

            @else

                <div class="alert alert-danger alert-fill alert-close alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    Error - Please resend the verification email.
                </div>
            @endif

            @if(!$user)
            <form action="{{route('auth.resend-email-verification')}}">
                {!! csrf_field() !!}
                <div class="form-group login-form">
                    <div class="form-control-wrapper form-control-icon-left {{ $errors->has('password') ? ' form-group-error' : '' }}" style="margin-bottom: 30px;">
                        <input type="text" name="email" class="form-control" placeholder="Email">
                        <i class="fa fa-envelope-o"></i>
                    </div>
                    <button type="submit" class="btn btn-block">Resend verification email</button>
                    <a href="{{ route('auth.register') }}" class="btn btn-block btn-primary-outline">Create an account</a>
                    <p class="help-block text-center">If you forgot your username/password <a class="sign-up" href="{{route('auth.forgot-password')}}">Click here</a></p>
                </div>
            </form>
            @else
                <a href="{{ route('account.index') }}" class="btn btn-block btn-primary-outline">Go to your account</a>
            @endif
        </div>
    </div>
@stop