@include('dashboard.includes.header')
<body>


<div class="o-page" id="app">
    <div class="o-page__sidebar js-page-sidebar">
        @include('dashboard.includes.sidebar')
    </div>
    <main class="o-page__content">

        <span id="error-messages"></span>

        @include('dashboard.includes.header-nav')

        <div class="container">

            @widget(\App\Widgets\ChecklistWarning::class, ['userId' => $payment->loan->user_id])

            <div class="row">
                <div class="col-md-4">
                    <div class="c-card" data-mh="dashboard3-cards">
                        <h4>Payment Status: {{$payment->status}}</h4>
                        <p class="u-mb-medium">

                            @if($payment->status !=='paid')

                                @if(\App\NodCredit\Account\Policies\LoanPaymentPolicy::canMarkAsPaid(app(\App\NodCredit\Account\User::class)))
                                    <a class="c-btn c-btn--primary u-mb-small" href="{{route('mainframe.payments')}}?markAsPaid={{$payment->id}}" onclick="return confirm('You are about to mark this payment as paid, make sure you have receieved the fund before performing this action. \n\nClick ok to continue or Cancel to abort.')">Mark as Paid</a>
                                @endif

                                @if(\App\NodCredit\Account\Policies\LoanPaymentPolicy::canIncreaseAmount(app(\App\NodCredit\Account\User::class)))
                                    <admin-loan-payment-amount :id="'{{ $payment->id }}'"></admin-loan-payment-amount>
                                @endif

                            @endif

                        </p>

                        <br><br>
                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Amount:</p>
                                <span class="u-text-small">NGN {{ number_format($payment->amount, 2) }}</span>
                            </div>
                        </div>

                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Month:</p>
                                <span class="u-text-small">{{$payment->payment_month}}</span>
                            </div>
                        </div>

                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Due Date:</p>
                                <span class="u-text-small">{{$payment->dueDate()}}</span>
                            </div>
                        </div>

                        @if($payment->failedLog->count() && $payment->status !== 'paid' )

                            <h5>Billing Failure Messages</h5>
                            <hr>

                            <ul>
                                @foreach($payment->failedLog as $failedInfo)
                                    <li>{{$failedInfo->res}} <small>{{$failedInfo->created_at}}</small></li>
                                @endforeach
                            </ul>

                        @endif

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="c-card">

                        <h4>Change billing date</h4>
                        <form action="{{route('account.loans.payments.set-payment-date', $payment->id)}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="c-field u-mb-small">
                                <label class="c-field__label">Select a new date</label>
                                <input type="date" name="new_date" class="c-input" placeholder="select new date" value="{{$payment->due_at ? date('Y-m-d', strtotime($payment->due_at)) : date('Y-m-d')}}">
                                @if($errors->first('new_date'))
                                    <p style="color: red;">{{$errors->first('new_date')}}</p>
                                @endif
                            </div>

                            <div class="c-field u-mb-small">
                                <button class="c-btn c-btn--fullwidth c-btn--info" type="submit">Set billing date</button>
                            </div>
                            <p>Instead of the original due date of <strong>{{$payment->dueDate()}}</strong>, the system will use this one.</p>
                        </form>

                        @if($payment->isDefault())
                            <admin-loan-payment-penalty :id="'{{ $payment->id }}'"></admin-loan-payment-penalty>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="c-card">
                        <div class="u-text-center">
                            <h5>{{$payment->loan->owner->name}}</h5>
                            <p>
                                @if($payment->loan->owner->role == 'user')
                                    Regular Customer
                                @elseif($payment->loan->owner->role == 'partner')
                                    Investor
                                @else
                                    Administrator
                                @endif
                            </p>
                        </div>

                        <span class="c-divider u-mv-small"></span>

                        <span class="c-text--subtitle">Email Address</span>
                        <p class="u-mb-small u-text-large">{{$payment->loan->owner->email}}</p>

                        <span class="c-text--subtitle">Phone NUMBER</span>
                        <p class="u-mb-small u-text-large">{{$payment->loan->owner->phone}}</p>

                        <span class="c-text--subtitle">BVN</span>
                        <p class="u-mb-small u-text-large">{{$payment->loan->owner->bvn}}</p>

                        <span class="c-text--subtitle">Gender</span>
                        <p class="u-mb-small u-text-large">{{$payment->loan->owner->gender}}</p>

                        <span class="c-text--subtitle">Date of Birth</span>
                        <p class="u-mb-small u-text-large">{{$payment->loan->owner->dob}}</p>

                    </div>
                </div>


            </div>

            <div class="row">
                <div class="col-md-8">
                    <admin-loan-payment-parts
                            :id="'{{ $payment->id }}'"
                            :can-add="{{ \App\NodCredit\Account\Policies\PartPaymentPolicy::canCreate(app(\App\NodCredit\Account\User::class)) ? 'true' : 'false' }}"
                    />
                </div>

                <div class="col-md-4">
                    <div class="c-card">
                        <h4>Uploaded Documents</h4>
                        <div class="c-card">
                            @foreach($documents as $document)
                                <div class="o-line u-pb-small u-mb-small u-border-bottom">
                                    <div class="o-media">
                                        <div class="o-media__body">
                                            <a href="{{ route('account.loan-documents.download', ['id' => $document->id]) }}" style="text-decoration: underline" onclick="return confirm('To download the document click ok');">
                                                <h6>{{$document->description}} ({{$document->document_extension}})</h6>
                                            </a>
                                            <p>Uploaded on {{$document->created_at}}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
