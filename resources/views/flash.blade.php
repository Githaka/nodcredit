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
