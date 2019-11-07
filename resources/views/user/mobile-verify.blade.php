@include('dashboard.includes.header')

<body>

<div class="o-page o-page--center">
    <div class="o-page__card">
        <div class="c-card c-card--center">
          @include('dashboard.includes.logo')

            <h4 class="u-mb-medium">Verify Your Number</h4>

            @if (session('error'))
           <div class="c-alert c-alert--danger">
            <span class="c-alert__icon">
              <i class="feather icon-slash"></i>
            </span>

                    <div class="c-alert__content">
                        <h4 class="c-alert__title">Error!</h4>
                        <p>{{session('error')}}</p>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="c-alert c-alert--success">
            <span class="c-alert__icon">
              <i class="feather icon-check"></i>
            </span>

                    <div class="c-alert__content">
                        <h4 class="c-alert__title">Success!</h4>
                        <p>{{session('success')}}</p>
                    </div>
                </div>
            @endif
            <form method="post" action="{{route('verify.mobile.post')}}">
                {!! csrf_field() !!}
                <div class="c-field">
                    <label class="c-field__label">Enter the OPT sent to your line (<string>****{{substr($user->phone, strlen($user->phone)-5)}}</string>)</label>
                    <input class="c-input u-mb-small" name="otp" type="text" placeholder="otp" required>

                    @if(session()->has('otpInfo'))
                        <p class="u-mb-small" style="text-align: center;">{{session()->get('otpInfo')}}</p>
                    @endif

                    <p class="js-otp-resend-wrapper u-mb-small text-center" style="display: none;">
                        <a class="js-otp-resend" href="?resend=otp" >Resend OTP</a>
                    </p >
                    <p class="u-mb-small js-otp-resend-timer-wrapper text-center">
                        You can resend OTP after <span class="js-otp-resend-timer">60</span>s
                    </p>

                </div>
                <button class="c-btn c-btn--fullwidth c-btn--info" type="submit">Continue</button>
            </form>
        </div>
    </div>
</div>

<script>
    window.document.addEventListener('DOMContentLoaded', function () {
        const timer = window.document.getElementsByClassName('js-otp-resend-timer')[0];
        let sec = 60;

        const interval = setInterval(function () {
            sec--;
            timer.innerText = sec;
        }, 1000);

        setTimeout(function () {
            window.document.getElementsByClassName('js-otp-resend-wrapper')[0].style.display = 'block';
            timer.closest('.js-otp-resend-timer-wrapper').remove();
            clearInterval(interval);
        }, sec * 1000);

    });
</script>

</body>
</html>
