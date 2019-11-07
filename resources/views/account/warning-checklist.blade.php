<?php
   $user = isset($user) ? $user : Auth::user();
    $checklist = $user->checkList();

?>
@if($checklist)
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="c-alert c-alert--warning u-mb-medium">
                    <span class="c-alert__icon">
                      <i class="feather icon-alert-triangle"></i>
                    </span>

                <div class="c-alert__content">
                    <h4 class="c-alert__title">Hi {{$user->name}}</h4>
                    <p>Loan can not be approved until the below check list is completed.</p>
                        @foreach($checklist as $section=>$item)
                            @foreach($item['items'] as $value)
                            <a href="{{$item['url']}}" class="dash-home c-badge c-badge--small c-badge--primary"><i class="c-field__icon u-color-warning feather icon-alert-circle"></i> {{$value['name']}}</a>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                @if(Auth::user()->role == 'admin' && Auth::user()->id !== $user->id)
                <div style="color: #FFF; background-color: orangered; padding: 10px;">You are logged in as an admin, please inform the user to update the check list</div>
                @endif
            </div>
    </div>

 @elseif(!$checklist && $user->canApplyForLoan() && !$user->applications()->count())

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="c-alert c-alert--success u-mb-medium">
            <span class="c-alert__icon">
              <i class="feather icon-check"></i>
            </span>

                <div class="c-alert__content">
                    <h4 class="c-alert__title">You are all set!</h4>
                    <p>To apply for a loan <a href="{{route('account.profile.apply')}}" class="c-btn c-btn--small c-btn--outline c-btn--success u-ml-xsmall">click here <i class="c-field__icon u-color-success feather icon-check"></i></a></p>
                </div>
            </div>
        </div>
    </div>

@endif
