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
                <div class="col-md-6">
                    <div class="c-card" data-mh="dashboard3-cards">
                        {{-- <p class="u-mb-medium">
                        </p> --}}
                        <h4 class="u-mb-medium">Tell us why</h4>

                        <form action="{{route('account.profile.liquidate.process', $investment->id)}}" method="post">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="liquidate_reason">Type your reason here</label>
                                        <textarea class="form-control c-input" rows="3" name="liquidate_reason" id="liquidate_reason" @if($investment->investment_ended)disabled @endif></textarea>
                                        @if($errors->has('liquidate_reason'))
                                            <p style="color: red;">{{$errors->first('liquidate_reason')}}</p>
                                        @endif
                                    </div>
                                    <div class="c-field u-mb-medium">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <a href="{{route('account.profile.invest')}}" class="c-btn c-btn--primary c-btn--fullwidth">Cancel</a>
                                            </div>
                                            <div class="col-md-6">
                                                <button class="c-btn c-btn--info c-btn--fullwidth" type="submit" @if($investment->investment_ended)disabled @endif>Liquidate Now</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="c-card" data-mh="dashboard3-cards">
                        <h4>Your investment Details</h4>
                        <div class="table-responsive">
                            {{-- <table class="c-table">

                                <tbody>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Amount</td>
                                    <td class="c-table__cell">N{{number_format($investment->amount,2)}}</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Tenor</td>
                                    <td class="c-table__cell">{{$investment->investment_tenor}}</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Invested Started On</td>
                                    <td class="c-table__cell">
                                        {{$investment->investment_started}} <br />
                                        @if($investment->investment_started)
                                            {{$investment->investment_started->diffInDays(now())}} days ago <br />
                                            <strong style="color: green;">Profit: N{{number_format($investment->calculatePossibleProfit(),2)}}</strong>
                                        @else
                                            0 days
                                        @endif
                                    </td>
                                </tr>
                                </tbody>
                            </table> --}}

                            <div class="c-chart__legends u-mt-medium">
                    <div class="row">
                      <div class="col-6">
                        <span class="c-chart__legend">
                          <i class="c-chart__legend-icon u-bg-info"></i>You invested a total amount of <strong>N{{number_format($investment->amount,2)}}</strong>
                        </span>
                      </div>
                      
                      <div class="col-6">
                        <span class="c-chart__legend">
                          <i class="c-chart__legend-icon u-bg-success"></i>Your investment was for <strong>{{$investment->investment_tenor}}</strong> days and you started on <strong>{{$investment->investment_started ? $investment->investment_started->formatLocalized("%A %d %B %Y") : 0}}</strong>
                        </span>
                      </div>
                    </div>
                  
                    <div class="row u-mt-xsmall">
                      <div class="col-6">
                        <span class="c-chart__legend">
                          <i class="c-chart__legend-icon u-bg-info"></i>@if($investment->investment_started)
                                            Your investment has ran for <strong>{{$investment->investment_started->diffInDays(now())}}</strong> days ago
                                        @else
                                            <strong>0 days</strong>
                                        @endif
                        </span>
                      </div>
                      
                      <div class="col-6">
                        <span class="c-chart__legend">
                          <i class="c-chart__legend-icon u-bg-success"></i>You will be paid out Profit: N <strong>{{number_format($investment->calculatePossibleProfit(),2)}}</strong> if you liquidate now.
                        </span>
                      </div>
                    </div>
                  </div>
                        </div>
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
