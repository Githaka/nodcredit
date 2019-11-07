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
                        <h4>Loan Pay Out</h4>
                        @if(!$canBePaid)
                                <div class="c-alert c-alert--danger">
                                <span class="c-alert__icon">
                                  <i class="feather icon-slash"></i>
                                </span>

                                <div class="c-alert__content">
                                    <h4 class="c-alert__title">Problem! account can not be paid</h4>
                                    <p>This account is not ready to be paid. See error message below.</p>
                                    <ul>
                                     @foreach($recipient->getRequiredDocuments() as $document)
                                         <li>* {{$document}}</li>
                                     @endforeach
                                    </ul>
                                </div>
                        </div>
                        @endif
                        <h5>Current Status: <small class="c-badge c-badge--success c-badge--small">{{$loan->status}}</small></h5>
                        <p class="u-mb-medium">Loan of  <strong>{{$loan->amount()}}</strong> by <strong>{{$loan->owner->name}}</strong> on <strong>{{$loan->created_at}}</strong>. <a href="{{route('mainframe.loans')}}">(Back)</a></p>



                        <form action="{{route('mainframe.loans.payments.transfer', $loan->id)}}" method="post">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-xl-4">

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="amount">Payout Amount</label>
                                        <input class="c-input" value="{{number_format($loan->amount_requested)}}" type="text" id="amount" disabled>
                                    </div>


                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="investor">Select Investor</label>
                                        <div class="c-select">
                                            <select class="c-select__input" name="investor">
                                                <option value="">--No investor--</option>
                                                @foreach($investors as $vname=>$vid)
                                                <option value="{{$vid}}">{{$vname}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if($errors->has('investor'))
                                            <p style="color: red;">{{$errors->first('tenor')}}</p>
                                        @endif
                                    </div>

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="password">Enter Your Account Password</label>
                                        <input class="c-input" value="" name="password" type="password" id="password" placeholder="Enter password to confirm transfer">
                                        @if($errors->has('password'))
                                            <p style="color: red;">{{$errors->first('password')}}</p>
                                        @endif
                                    </div>



                                    <div class="c-field u-mb-medium">
                                        <div class="c-alert c-alert--warning u-mb-medium">
                                            <span class="c-alert__icon">
                                              <i class="feather icon-alert-triangle"></i>
                                            </span>

                                            <div class="c-alert__content">
                                                <h4 class="c-alert__title">Warning! you should read the below message first</h4>
                                                <p>Clicking the transfer button will transfer the sum of <strong>N{{number_format($loan->amount_requested)}}</strong> to <strong>{{$loan->owner->name}}</strong> via PayStack. Review the transaction carefully before you click the button.</p>
                                            </div>
                                        </div>


                                        <button class="c-btn c-btn--info c-btn--fullwidth" type="submit">Transfer Fund N{{number_format($loan->amount_requested)}}</button>

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
