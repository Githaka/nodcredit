@include('dashboard.includes.header')

<body>

<div class="o-page o-page--center">
    <div class="o-page__card">
        <div class="c-card c-card--center">
            @include('dashboard.includes.logo')

            <h4 class="u-mb-medium">Sign Up to Get Started</h4>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="post" action="{{route('ui.auth.register.process')}}">
                {!! csrf_field() !!}

                <div class="c-field u-mb-small">
                    <label class="c-field__label">What do you want to do?</label>
                    <div class="c-select">
                        <select class="c-select__input" name="want" id="user-type">
                            <option value="1" @if(old('want') == '1' || request('want') === '1') selected="selected" @endif>I want a loan</option>
                            <option value="2"  @if(old('want') === '2' || request('want') === '2') selected="selected" @endif>I want to invest</option>
                        </select>
                    </div>
                </div>

                <div class="c-field" id="bvn-holder">
                    <label class="c-field__label">BVN</label>
                    <input class="c-input u-mb-small" value="{{old('bvn')}}" name="bvn" type="text" placeholder="Type your BVN here">
                </div>

                <div class="c-field">
                    <label class="c-field__label">Email Address</label>
                    <input class="c-input u-mb-small" value="{{old('email')}}" name="email" type="email" placeholder="e.g. abah@nodcredit.com" required>
                </div>


                <div class="c-field u-mb-small">
                    <label class="c-field__label">Phone</label>
                    <input class="c-input" value="{{old('phone')}}" name="phone" type="text" placeholder="Phone number" required>
                    <small id="phoneHelpBlock" class="form-text text-muted">
                        Verification is required. You must use a valid mobile line.
                    </small>
                </div>

                <div class="c-field u-mb-small">
                    <label class="c-field__label">
                        <input type="checkbox" value="1" name="agree" {{ old('agree') ? 'checked' : '' }}>
                        I agree to the <a target="_blank" href="/term-conditions">Terms and Conditions</a>
                    </label>
                </div>

                <button class="c-btn c-btn--fullwidth c-btn--info" type="submit">Register</button>

                <p style="margin-top: 10px;">
                    Already registered? <a href="{{route('login')}}">Log in</a>
                </p>
            </form>
        </div>
    </div>
</div>

<!-- Main JavaScript -->
<script src="{{asset('dashboard/js/mad.min.js')}}"></script>

<script>
    function toggleBVN() {
        var regType = $('#user-type').val();
        if(regType === '2') {
            $('#bvn-holder').hide();
        } else {
            $('#bvn-holder').show();
        }
    }

    $(document).ready(function(){
        toggleBVN();
        $('#user-type').change(function(){
            toggleBVN();
        });
    });
</script>

</body>
</html>
