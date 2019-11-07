@if ($user->canApplyForLoan() AND ! $user->hasActiveLoan())

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="c-alert c-alert--success u-mb-medium">
                <span class="c-alert__icon"><i class="feather icon-check"></i></span>

                <div class="c-alert__content">
                    <h4 class="c-alert__title">You are all set!</h4>
                    <p>To apply for a loan <a href="{{route('account.profile.apply')}}" class="c-btn c-btn--small c-btn--outline c-btn--success u-ml-xsmall">click here <i class="c-field__icon u-color-success feather icon-check"></i></a></p>
                </div>
            </div>
        </div>
    </div>

@endif
