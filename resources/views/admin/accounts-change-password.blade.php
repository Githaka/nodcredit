@include('dashboard.includes.header')
<body>


<div class="o-page">
    <div class="o-page__sidebar js-page-sidebar">
        @include('dashboard.includes.sidebar')
    </div>
    <main class="o-page__content">

        <span id="error-messages"></span>

        @include('dashboard.includes.header-nav')

        <div class="container">

            <div class="row">
                <div class="col-md-12">
                    <div class="c-card" data-mh="dashboard3-cards">
                        <h4>Change Password for {{$user->name}}</h4>
                        <p class="u-mb-medium">Use a secure password.</p>

                        <form action="{{route('admin.change.password.store', $user->id)}}" method="post">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-xl-4">

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="amount">New Password</label>
                                        <input class="c-input" name="password" value="" type="password" id="password" placeholder="Type a new password">
                                        @if($errors->has('password'))
                                            <p style="color: red;">{{$errors->first('password')}}</p>
                                        @endif
                                    </div>



                                    <div class="c-field u-mb-medium">
                                        <label class="c-switch u-mr-small">
                                            <input class="c-switch__input" id="switch1" type="checkbox" name="emailPassword" value="1">
                                            <span class="c-switch__label">Email new password to user</span>
                                        </label>
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

            @include('dashboard.includes.footer')

        </div>
    </main>
</div>

<!-- Main JavaScript -->
@include('dashboard.includes.account-footer')

</body>
</html>
