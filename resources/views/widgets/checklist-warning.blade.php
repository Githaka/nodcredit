@if ($showChecklist)
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="c-alert c-alert--warning u-mb-medium">
                <span class="c-alert__icon"><i class="feather icon-alert-triangle"></i></span>

                <div class="c-alert__content">
                    <h4 class="c-alert__title">Hi {{ $user->getName() }}</h4>

                    @if (count($requirements))
                        <div class="u-mb-small">
                            <p>Loan can not be approved until the below check list is completed:</p>

                            @foreach($requirements as $section => $item)
                                @foreach($item['items'] as $value)
                                    <a href="{{$item['url']}}" class="dash-home c-badge c-badge--small c-badge--{{ array_get($item, 'style', 'primary') }}">
                                        <i class="c-field__icon u-color-warning feather icon-alert-circle"></i>
                                        {{$value['name']}}
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    @endif

                    @if (count($messages))
                        <p>Please, notice:</p>

                        @foreach($messages as $section => $item)
                            @foreach($item['items'] as $value)
                                <a href="{{$item['url']}}" class="dash-home c-badge c-badge--small c-badge--primary">
                                    <i class="c-field__icon u-color-warning feather icon-alert-circle"></i>
                                    {{$value['name']}}
                                </a>
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </div>

    @if ($showAdminInform)
        <div class="row justify-content-center">
            <div class="col-md-10 checklist-warning-admin-inform">
                You are logged in as an admin, please inform the user to update the check list
            </div>
        </div>
    @endif


@endif
