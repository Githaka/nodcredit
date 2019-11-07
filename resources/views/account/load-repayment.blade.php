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

                @widget(\App\Widgets\DisburseWarning::class)

                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="c-alert c-alert--info alert u-mb-medium">
            <span class="c-alert__icon">
              <i class="feather icon-info"></i>
            </span>

            <div class="c-alert__content">
              <p class="c-alert__title u-mb-zero">You can pause your NodCredit loan and you can also pay us back in splits - repay in small chunks</p>
            </div>

            <button class="c-close" data-dismiss="alert" type="button">×</button>
          </div>
                    </div>
                </div>
                {{-- <div class="row justify-content-center">
                    <div class="col-md-11">
                        <div class="lrp-alert c-alert c-alert--warning alert u-mb-medium">
                            <span class="c-alert__icon">
                                <i class="feather icon-info"></i>
                            </span>
                            <div class="c-alert__content">
                                <form action="{{route('account.loans.repayment.pause-payment')}}" method="post"
                onsubmit="return confirm('Are you sure about this action? Click Ok to confirm.');">
                {!! csrf_field() !!}
                <input type="hidden" name="action" value="pauseLoan">
                <input type="hidden" name="payment" value="{{$nextPayment->id}}">
                <p>You can pause your loan repayment on NodCredit. We will charge you
                    <strong>15%</strong>
                    of scheduled repayment for that month...</p>
                <br>
                <select name="card" id="" class="c-input">
                    <option value="" disabled selected>Select a card</option>
                    @foreach(Auth::user()->cards as $card)
                    <option value="{{$card->id}}">{{$card->card_type}} -
                        {{$card->card_number}}
                        ({{$card->exp_month}}/{{$card->exp_year}})</option>
                    @endforeach
                </select>
                <button class="c-btn c-btn--small u-ml-small">Pause</button>
                </form>
            </div>

            <button class="c-close" data-dismiss="alert" type="button">×</button>
    </div>
    </div>
    </div> --}}
    <div class="row" style="padding: 40px 0px;">
        <div class="col-md-12">
            <p style="text-align: center; color: #000;"><span style="font-size: 120%">Next Payment
                    {{$nextPayment->due_at->formatLocalized("%A %d %B %Y")}}</span></p>
            <p style="text-align: center;">
                <strong style="color: #0c5fc7;">
                    <span style="font-size: 300%">{{$nextPayment->getAmount()}}</span>
                </strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 lrp-col">
            <nav class="c-tabs">
                <div class="c-tabs__list nav nav-tabs" id="myTab" role="tablist">
                    <a class="c-tabs__link active" id="repay-loan-tab" data-toggle="tab" href="#repay-loan" role="tab"
                        aria-controls="repay-loan" aria-selected="true">Repay Loan</a>
                    <a class="c-tabs__link" id="pause-loan-tab" data-toggle="tab" href="#pause-loan" role="tab"
                        aria-controls="pause-loan" aria-selected="false">Pause Loan</a>
                </div>
                <div class="c-tabs__content tab-content" id="nav-tabContent">
                    <div class="c-tabs__pane active" id="repay-loan" role="tabpanel" aria-labelledby="repay-loan-tab">
                        @if(!$loan)
                        <h4>No Loan Re-payment Record</h4>

                        <p class="u-mb-small">
                            Your loan re-payment plan will be displayed here.
                        </p>
                        @else
                        <h4>Loan Repayment Record</h4>
                        <p style="margin-bottom:30px;">You can reconcile your active loans at any time.</p>

                        @foreach($loan->payments as $payment)
                        <form action="{{route('account.loans.repayment.bill')}}" method="post"
                            onsubmit="return confirm('Click Ok to confirm your action.');">
                            {!! csrf_field() !!}
                            <input type="hidden" name="paynow" value="{{$payment->id}}">
                            <div class="o-line u-pb-small u-mb-small u-border-bottom">
                                <div class="row" style="width:100%;">
                                    <div class="col-md-9">
                                        <div class="o-media">
                                            <div class="o-media__body">
                                                <div class="row">
                                                    @if($payment->status !== 'paid')
                                                    <div class="col-md-5">
                                                        <div class="c-field has-icon-right">
                                                            <input type="text" name="amount"
                                                            value="{{$payment->getAmount()}}" class="c-input">
                                                            <i class="c-field__icon u-color-warning feather icon-alert-circle c-tooltip c-tooltip--left" aria-label="You can change the amount">i</i>
                                                        </div>
                                                        
                                                    </div>
                                                    <div class="col-md-7">
                                                        <select name="card" id="" class="c-input">
                                                            <option value="" disabled selected>Select a card
                                                            </option>
                                                            @foreach(Auth::user()->cards as $card)
                                                            <option value="{{$card->id}}">
                                                                {{$card->card_type}} -
                                                                {{$card->card_number}}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @else
                                                    <div class="col-md-6 lrp-col">
                                                        <h6>{{$payment->getAmount()}}</h6>
                                                    </div>
                                                    @endif
                                                    <div class="col-md-12">
                                                        <p style="font-size:0.75rem"><a
                                                                href="?transactionInfo={{$payment->id}}">payment
                                                                info</a></p>
                                                        {{-- <p>{{$payment->due_at->formatLocalized("%A %d %B %Y")}}
                                                        </p> --}}
                                                    </div>
                                                </div>
                                                {{-- @if($payment->status !== 'paid')
                                                    <input type="text" name="amount" value="{{$payment->getAmount()}}"
                                                class="c-input">

                                                <select name="card" id="" class="c-input">
                                                    @foreach(Auth::user()->cards as $card)
                                                    <option value="{{$card->id}}">{{$card->card_type}} -
                                                        {{$card->card_number}}
                                                        ({{$card->exp_month}}/{{$card->exp_year}})</option>
                                                    @endforeach
                                                </select>
                                                @else
                                                <h6>{{$payment->getAmount()}}</h6>
                                                @endif
                                                <p><a href="?transactionInfo={{$payment->id}}">payment
                                                        info</a></p>
                                                <p>{{$payment->due_at->formatLocalized("%A %d %B
                                                        %Y")}}</p> --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 lrp-col text-center">
                                        <h6>
                                            @if($payment->status == 'scheduled')
                                            <button class="c-btn c-btn--success c-btn--small">Pay
                                                Now</button>
                                            @endif<br>
                                            <span
                                                class="loan-status c-badge c-badge--small c-badge--{{$payment->getBadgeCode()}} c-tooltip c-tooltip--bottom"
                                                aria-label="{{$payment->due_at->formatLocalized("%A %d %B %Y")}}">{{$payment->status}}
                                                <i
                                                    class="c-field__icon u-color-{{$payment->getBadgeCode()}} feather icon-alert-circle"></i></span>
                                        </h6>
                                    </div>
                                </div>



                            </div>
                        </form>
                        @endforeach
                        {{-- <div class="c-table-responsive@wide" style="margin-top: 50px;">
                                <table class="c-table">
                                    <thead class="c-table__head">

                                        <tr class="c-table__row">
                                            <th class="c-table__cell c-table__cell--head">Amount</th>
                                            <th class="c-table__cell c-table__cell--head">Due Date</th>
                                            <th class="c-table__cell c-table__cell--head">Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($loan->payments as $payment)
                                        <tr class="c-table__row">
                                            <td class="c-table__cell">
                                                <a href="?transactionInfo={{$payment->id}}">{{$payment->getAmount()}}</a>
                        </td>
                        <th class="c-table__cell">{{$payment->due_at->formatLocalized("%A %d %B
                                                %Y")}}
                            @if($payment->status == 'scheduled')<a href="?paynow={{$payment->id}}"
                                class="c-btn c-btn--success c-btn--small"
                                onclick="return confirm('Click Ok to confirm your action')">Pay
                                Now</a>@endif</th>
                        <td class="c-table__cell">
                            <a href="?transactionInfo={{$payment->id}}"><span
                                    class="c-badge c-badge--small c-badge--{{$payment->getBadgeCode()}}">{{$payment->status}}</span></a>
                        </td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                    </div> --}}
                    @endif
                </div>
                <div class="c-tabs__pane" id="pause-loan" role="tabpanel" aria-labelledby="pause-loan-tab">
                    <h4>Pause your Loan</h4>
                    <p class="u-mb-small">
                        You can pause your loan repayment on NodCredit. We will charge you <strong>15%</strong> of scheduled repayment for that month...
                    </p>
                    <form action="{{route('account.loans.repayment.pause-payment')}}" method="post" class="u-mb-small"
                        onsubmit="return confirm('Are you sure about this action? Click Ok to confirm.');">
                        {!! csrf_field() !!}
                        <input type="hidden" name="payment" value="{{$nextPayment->id}}">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="c-select pl-select">
                                    <select name="card" id="" class="c-select__input">
                                        <option value="" disabled selected>Select a card</option>
                                        @foreach(Auth::user()->cards as $card)
                                        <option value="{{$card->id}}">{{$card->card_type}} -
                                            {{$card->card_number}}
                                            ({{$card->exp_month}}/{{$card->exp_year}})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">

                                    <button
                                            @if (! $application->canPauseByUser() OR $application->getAccountUser()->isDefaulter()) disabled @endif
                                            class="c-btn c-btn--medium c-btn--fullwidth"
                                    >Pause</button>
                            </div>
                        </div>
                    </form>

                    @if (! $application->canPauseByUser())
                        <p><b><i class="feather icon-alert-circle"></i> You have exceeded your pause limit.</b></p>
                    @elseif ($application->getAccountUser()->isDefaulter())
                        <p><b><i class="feather icon-alert-circle"></i> Loan is overdue and can not be paused.</b></p>
                    @endif

                </div>

        </div>
        </nav>
    </div>



    <div class="col-md-6">
        <div class="c-card" data-mh="dashboard3-cards">
            <h4>Loan Payment Transaction</h4>
            <p class="u-mb-medium">To view a transaction, click on any of the payment from the left</p>
            <div class="c-feed">
                @if($transactions)
                @foreach($transactions as $transaction)

                <div class="c-feed__item c-feed__item--{{$transaction->status == 'successful' ? 'success': 'danger'}}">
                    <p>{{$transaction->pay_for}} {{$transaction->getAmount()}} <br />
                        <small><strong>On: {{$transaction->created_at}}</strong> <small
                                class="c-badge c-badge--small c-badge--{{$transaction->status == 'successful' ? 'success': 'danger'}}">{{$transaction->status}}</small></small>
                        @if($transaction->status == 'failed')
                        <br />
                        <strong>{{$transaction->response_message}}</strong>
                        @endif
                    </p>
                </div>


                @endforeach
                @endif
            </div><!-- // .c-feed -->

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