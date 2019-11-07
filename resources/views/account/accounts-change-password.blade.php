@extends('account.layout.main')

@section('content-container-body')
    <div class="row">
        <div class="col-md-12">
            <div class="c-card" data-mh="dashboard3-cards">
                <h4>Change Password your password</h4>
                <p class="u-mb-medium">
                    Use a secure password. <br>
                    Suggested password: <b>{{ str_random(10) }}</b>
                </p>

                <form action="{{route('user.change.password.store')}}" method="post">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-xl-4">

                            @if(!Auth::user()->force_change_pwd)

                                <div class="c-field u-mb-medium">
                                    <label class="c-field__label" for="amount">Enter your current password</label>
                                    <input class="c-input" name="current_password" value="" type="password" id="current_password" placeholder="Enter your current password">
                                    @if($errors->has('current_password'))
                                        <p style="color: red;">{{$errors->first('current_password')}}</p>
                                    @endif
                                </div>
                            @endif

                            <div class="c-field u-mb-medium">
                                <label class="c-field__label" for="amount">New Password</label>
                                <input class="c-input" name="password" value="" type="password" id="password" placeholder="Type a new password">
                                @if($errors->has('password'))
                                    <p style="color: red;">{{$errors->first('password')}}</p>
                                @endif
                            </div>

                            <div class="c-field u-mb-medium">
                                <label class="c-field__label" for="amount">Confirm New Password</label>
                                <input class="c-input" name="password_confirmation" value="" type="password" id="password_confirmation" placeholder="Confirm new password">
                                @if($errors->has('password_confirmation'))
                                    <p style="color: red;">{{$errors->first('password_confirmation')}}</p>
                                @endif
                            </div>

                            <div class="c-field u-mb-medium">
                                <p>Your login phone is: {{$user->phone}}</p>
                            </div>


                            <div class="c-field u-mb-medium">
                                <button class="c-btn c-btn--info c-btn--fullwidth" type="submit">Change Password</button>
                            </div>

                        </div>


                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection
