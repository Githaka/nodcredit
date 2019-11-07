@include('dashboard.includes.header')

<body>

<div class="o-page o-page--center">
  <div class="o-page__card">
    <div class="c-card c-card--center">
      @include('dashboard.includes.logo')

      <h4 class="u-mb-medium">Set A New Password</h4>

      @include('flash')
      <form method="post" action="{{route('auth.reset-password-process', $token)}}">
        {!! csrf_field() !!}
        <p style="margin-bottom: 10px;">
          Password must at least 8 characters. <br>
          Suggested password: <b>{{ str_random(10) }}</b>
        </p>

        @if ($errors->any())
          <div class="alert alert-danger" style="text-align: left;">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <div class="c-field">
          <label class="c-field__label">Password</label>
          <input class="c-input u-mb-small" name="password" type="password" placeholder="Password" required>
        </div>

        <div class="c-field">
          <label class="c-field__label">Password Confirmation</label>
          <input class="c-input u-mb-small" name="password_confirmation" type="password" placeholder="Password confirmation" required>
        </div>

        <button class="c-btn c-btn--fullwidth c-btn--info" type="submit">Update Password</button>
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
