@include('dashboard.includes.header')

<body>

<div class="o-page o-page--center">
    <div class="o-page__card">
        <div class="c-card c-card--center">
          @include('dashboard.includes.logo')

            <h4 class="u-mb-medium">Welcome Back :)</h4>

                @include('flash')
            <form method="post" action="{{route('ui.auth.login.process')}}">
                {!! csrf_field() !!}
                <div class="c-field">
                    <label class="c-field__label">Email or Phone</label>
                    <input class="c-input u-mb-small" name="identity" type="text" placeholder="email or phone" required>
                </div>

                <div class="c-field">
                    <label class="c-field__label">Password</label>
                    <input class="c-input u-mb-small" name="password" type="password" placeholder="Password" required>
                </div>

                <button class="c-btn c-btn--fullwidth c-btn--info" type="submit">Login</button>
                <p style="margin-top: 10px;">
                    Don`t have an account? <a href="{{route('ui.auth.register')}}">Register</a>
                </p>

                <p style="margin-top: 10px;">
                    Forgot your password? <a href="{{route('auth.forgot-password')}}">Reset</a>
                </p>
            </form>
        </div>
    </div>
</div>

<!-- Main JavaScript -->
<script src="{{asset('dashboard/js/mad.min.js')}}"></script>

</body>
</html>
