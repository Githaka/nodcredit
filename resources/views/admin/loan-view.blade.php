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

            @widget(\App\Widgets\ChecklistWarning::class, ['userId' => $loan->user_id])

            <div class="row">
                <div class="col-md-4">
                    <div class="c-card" data-mh="dashboard3-cards">
                        <h4>Loan Status: {{$loan->status}}</h4>
                        <p class="u-mb-medium">
                        <div class="c-dropdown dropdown">
                            <a href="#" class="c-btn c-btn--info has-icon dropdown-toggle" id="dropdownMenuToggleModal" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" role="button">
                                Change Loan Status <i class="c-btn__icon feather icon-chevron-down"></i>
                            </a>

                            <div class="c-dropdown__menu dropdown-menu dropdown-menu" aria-labelledby="dropdownMenuToggleModal" x-placement="bottom-end" style="position: absolute; transform: translate3d(-21px, 42px, 0px); top: 0px; left: 0px; will-change: transform;">
                                @foreach(['new','processing','approved','rejected','unknown','completed','approval'] as $status)
                                     <a class="c-dropdown__item dropdown-item" href="?changeStatus={{$status}}&loan={{$loan->id}}" onclick="return confirm('Are you really sure you want to change this loan status?')">{{ucfirst($status)}}</a>
                                @endforeach
                            </div>
                        </div>
                        </p>

                        <br><br>
                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Loan Amount:</p>
                                <span class="u-text-small">{{$loan->amount()}}</span>
                            </div>
                        </div>

                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Loan Tenor:</p>
                                <span class="u-text-small">{{$loan->tenor}}</span>
                            </div>
                        </div>

                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Interest Rate:</p>
                                <span class="u-text-small">{{$loan->interest_rate}}%</span>
                            </div>
                        </div>

                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Application Date:</p>
                                <span class="u-text-small">{{$loan->created_at}}</span>
                            </div>
                        </div>

                        <div class="o-media u-mb-small">
                            <div class="o-media__body u-flex u-justify-between">
                                <p>Approved On:</p>
                                <span class="u-text-small">{{$loan->approved_at}}</span>
                            </div>
                        </div>


                        @if (in_array($loan->status, ['new', 'processing', 'waiting']) AND !$loan->owner->checkList())

                            <h4 class="u-mb-small">Eligible Amount</h4>

                            @if ($loan->status === 'waiting')
                                @if ($loan->amount_allowed_at)
                                <p class="u-mb-small">
                                    You have sent new amount confirmation mail to the customer at <b>{{ $loan->amount_allowed_at }}</b>.
                                </p>
                                @endif

                                @if ($loan->amount_allowed)
                                    <div class="o-media u-mb-small">
                                        <div class="o-media__body u-flex u-justify-between">
                                            <p>Eligible Amount</p>
                                            <span class="u-text-small">NGN {{ number_format($loan->amount_allowed) }}</span>
                                        </div>
                                    </div>
                                @endif

                            @endif

                            <admin-loan-send-new-amount :id="'{{ $loan->id }}'"></admin-loan-send-new-amount>

                        @endif

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="c-card" >

                        <h4>Upload Loan Documents</h4>
                        <form action="{{route('account.loans.upload-document', $loan->id)}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="c-field u-mb-small">
                                <label class="c-field__label">Select Document Type</label>
                                <div class="c-select">
                                    <select class="c-select__input js-loan-application-document-type" name="documentType">
                                        @foreach($documentTypes as $documentType)
                                                <option value="{{$documentType->id}}">{{$documentType->name}}</option>
                                        @endforeach
                                    </select>

                                    @if($errors->has('documentType'))
                                        <p style="color: red;">{{$errors->first('documentType')}}</p>
                                    @endif

                                </div>
                            </div>

                            <div class="c-field u-mb-small">
                                <label class="c-field__label">Select file to upload</label>
                                <div class="c-field">
                                    <input type="file" name="file" id="" class="c-select__input" />
                                    @if($errors->has('file'))
                                        <p style="color: red;">{{$errors->first('file')}}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="c-field u-mb-small js-loan-application-document-password" style="display: none;" >
                                <label class="c-field__label">Password (if document is locked)</label>
                                <div class="c-field">
                                    <input type="text" name="unlock_password" class="c-select__input" />
                                </div>
                            </div>


                            <div class="c-field u-mb-small">
                                <button class="c-btn c-btn--fullwidth c-btn--info" type="submit">Upload</button>
                            </div>

                            <p>Customer has uploaded {{$documentInfo->uploadedRequired}} of {{$documentInfo->required}} required documents</p>
                        </form>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="c-card" data-mh="dashboard3-cards">
                        <h4>Uploaded Documents</h4>
                        <div class="c-card" data-mh="dashboard3-cards">
                            @foreach($loan->documents as $document)
                                <div class="o-line u-pb-small u-mb-small u-border-bottom">
                                    <div class="o-media">
                                        <div class="o-media__body">
                                            <a href="?downloadDocument={{$document->id}}" style="text-decoration: underline" onclick="return confirm('To download the document click ok');"> <h6>{{$document->description}} ({{$document->document_extension}})</h6></a>
                                            <p>Uploaded on {{$document->created_at}}</p>

                                            @if ($document->unlock_password)
                                                <p>Password: <b>{{ $document->unlock_password }}</b></p>

                                                @if ($document->is_unlocked)
                                                    <p><b style="color: green">Unlocked</b></p>
                                                @else
                                                    <p><b style="color: red">Locked</b></p>
                                                    <p>Unlock message: <b>{{ $document->unlock_response }}</b></p>
                                                @endif

                                            @endif

                                        </div>
                                    </div>
                                    <h6><a class="c-badge c-badge--small c-badge--danger" href="?deleteDocument={{$document->id}}" onclick="return confirm('Are you sure you want to delete this document?')">X</a></h6>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>

           <div class="row">
               <div class="col-md-4">
                   <div class="c-card">
                       <div class="u-text-center">
                           <div class="c-avatar c-avatar--large u-mb-small u-inline-flex">
                               <img class="c-avatar__img" src="{{$loan->owner->avatar_url}}" alt="{{$loan->owner->name}}">
                           </div>

                           <h5>{{$loan->owner->name}}</h5>
                           <p>
                               @if($loan->owner->role == 'user')
                                   Regular Customer
                               @elseif($loan->owner->role == 'partner')
                                   Investor
                               @else
                                   Administrator
                               @endif
                           </p>
                       </div>

                       <span class="c-divider u-mv-small"></span>

                       <span class="c-text--subtitle">Email Address</span>
                       <p class="u-mb-small u-text-large"><a href="{{route('admin.accounts.message', $loan->owner->id)}}">{{$loan->owner->email}}</a></p>

                       <span class="c-text--subtitle">Phone NUMBER</span>
                       <p class="u-mb-small u-text-large"><a href="{{route('admin.accounts.message', $loan->owner->id)}}">{{$loan->owner->phone}}</a></p>

                       <span class="c-text--subtitle">BVN</span>
                       <p class="u-mb-small u-text-large">{{$loan->owner->bvn}}</p>

                       <span class="c-text--subtitle">Gender</span>
                       <p class="u-mb-small u-text-large">{{$loan->owner->gender}}</p>

                       <span class="c-text--subtitle">Date of Birth</span>
                       <p class="u-mb-small u-text-large">{{$loan->owner->dob}}</p>


                   </div>
               </div>

               <div class="col-md-4">
                   <div class="c-card">
                       <h4>Payments</h4>
                       <p class="u-mb-medium">Payment schedule for this loan.</p>
                       <p class="u-mb-medium" style="text-align: center;">Expected payment NGN{{number_format($loan->totalExpectedPayback(),2)}} </p>

                       @foreach($loan->payments as $payment)
                           <div class="o-line u-pb-small u-mb-small u-border-bottom">
                               <div class="o-media">
                                   <div class="o-media__body">
                                       <h6>{{$payment->due_at}}</h6>
                                   </div>
                               </div>
                               <h6>{{$payment->getAmount()}}</h6>
                           </div>
                       @endforeach

                   </div>
               </div>

               <div class="col-md-4">
                   <div class="c-card">
                       <h4>Pay Out Information</h4>
                       <p class="u-mb-medium">You this section to track payment made to the customer`s account.</p>

                       @if(!$loan->paid_out)
                           <a href="{{route('mainframe.loans.payments', $loan->id)}}" class="c-btn c-btn--large c-btn--fullwidth">Pay Out</a>
                       @else


                           <p class="u-mb-medium">Loan was paid out on: <strong> {{$loan->paid_out}}</strong> by <strong>{{$loan->payer->name}}</strong></p>
                       @endif
                   </div>
               </div>
           </div>

            @include('account.loan-actions')


            <div class="row">
                <div class="col-12">
                    <div class="c-table-responsive@wide">
                        <div class="c-card" data-mh="dashboard3-cards">
                            <h3 class="u-mb-medium">Locations</h3>

                            @if ($locations->count())
                                <table class="c-table">
                                    <thead class="c-table__head">
                                    <tr class="c-table__row">
                                        <th class="c-table__cell c-table__cell--head">Date / Coordinates</th>
                                        <th class="c-table__cell c-table__cell--head">Addresses</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        @php $location =  $locations->first(); @endphp

                                        <tr class="c-table__row">
                                            <th class="c-table__cell">
                                                {{ $location->getCreatedAt() }}
                                                <br>
                                                <a target="_blank" href="https://www.google.com/maps/place/{{ $location->getLat() }},{{ $location->getLon() }}">
                                                    <i class="feather icon-map"></i> {{ $location->getLat() }}, {{ $location->getLon() }}
                                                </a>
                                            </th>
                                            <td class="c-table__cell">
                                                @if($location->isGeocodeStatusNew())
                                                    Geocoding... It may take few minutes. Please, wait and reload page.
                                                @elseif($location->hasResults())
                                                    <table class="c-table">
                                                        <thead class="c-table__head">
                                                        <tr class="c-table__row">
                                                            <th class="c-table__cell">#</th>
                                                            <th class="c-table__cell">Admin Levels</th>
                                                            <th class="c-table__cell">Address</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($location->getResults() as $key => $address)
                                                            <tr class="c-table__row">
                                                                <td class="c-table__cell">{{ $key+1 }}.</td>
                                                                <td class="c-table__cell" style="white-space: normal;">

                                                                    @if ($address->getCountry())
                                                                        {{ $address->getCountry()->getName() }},
                                                                    @endif

                                                                    @foreach($address->getAdminLevels() as $adminLevel)
                                                                        {{ $adminLevel->getName() }},
                                                                    @endforeach
                                                                </td>
                                                                <td class="c-table__cell" style="white-space: normal;">{{ $address->getFormattedAddress() }}</td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    No records
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="u-mt-small"><a target="_blank" href="{{route('admin.accounts.locations', $loan->owner->id)}}" class="c-btn">All locations</a></p>
                            @else
                                No records
                            @endif

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

<script>
    $(document).ready(function() {

        function refreshUnlockPasswordState() {

            // Bank statement
            if ($('.js-loan-application-document-type').val() === 'ca33a080-a32f-11e8-88df-3bf70087289e') {
                $('.js-loan-application-document-password').fadeIn();
            }
            else {
                $('.js-loan-application-document-password').fadeOut();
            }

            $('[data-mh]').matchHeight();
        }

        $('.js-loan-application-document-type').on('change', function () {
            refreshUnlockPasswordState();
        });


        refreshUnlockPasswordState();

    });
</script>

</body>
</html>
