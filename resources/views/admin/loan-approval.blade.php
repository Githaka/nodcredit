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
                <div class="col-md-12">
                    <div class="c-card" data-mh="dashboard3-cards">
                        <h4>Loan Approval</h4>
                        <p class="u-mb-medium">Loan of  <strong>{{$loan->amount()}}</strong> by <strong>{{$loan->owner->name}}</strong> on <strong>{{$loan->created_at}}</strong>. <a href="{{route('mainframe.loans.show', $loan->id)}}">(Cancel)</a></p>

                        <form action="{{route('mainframe.loans.approval.store', $loan->id)}}" method="post">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-xl-4">

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="amount">How much do you want to approve?</label>
                                        <input class="c-input" name="amount" value="{{$loan->amount_requested}}" type="text" id="amount" placeholder="10,0000">
                                        @if($errors->has('amount'))
                                            <p style="color: red;">{{$errors->first('amount')}}</p>
                                        @endif
                                    </div>

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="interest_rate">How much interest rate is on this loan? <br />(Default: %{{$interestRate}}, User: {{$loan->owner->getInterestRate()}}% based on score: {{$loan->owner->getScores()}})</label>
                                        <input class="c-input" name="interest_rate" value="{{$loan->interest_rate > 0? $loan->interest_rate : $interestRate}}" type="text" id="interest_rate" placeholder="15%">
                                        @if($errors->has('interest_rate'))
                                            <p style="color: red;">{{$errors->first('interest_rate')}}</p>
                                        @endif
                                    </div>

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="tenor">How many months is the customer expected to pay back?</label>
                                        <input class="c-input" name="tenor" value="{{$loan->tenor}}" type="text" id="tenor" placeholder="">
                                        @if($errors->has('tenor'))
                                            <p style="color: red;">{{$errors->first('tenor')}}</p>
                                        @endif
                                    </div>



                                    <div class="c-field u-mb-medium">
                                        <button class="c-btn c-btn--info c-btn--fullwidth" type="submit">Approve Loan</button>
                                        <p style="text-align: center; margin-top: 10px;">You are logged in as {{Auth::user()->name}}. All transactions is tied to your account</p>
                                    </div>

                                </div>


                            </div>
                        </form>


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
