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
                <div class="col-12">
                    <div class="c-table-responsive@wide">
                        <h4>Loan Applications</h4>
                        <p class="u-mb-medium">We have {{$totalLoans}} loan applications in the system</p>


                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Loan Amount</th>
                                <th class="c-table__cell c-table__cell--head">Owner</th>
                                <th class="c-table__cell c-table__cell--head">From Date</th>
                                <th class="c-table__cell c-table__cell--head">Status</th>
                                <th class="c-table__cell c-table__cell--head">Loan Type</th>
                                <th class="c-table__cell c-table__cell--head">Action</th>
                            </tr>
                            <form action="" method="get" id="filter-form" name="filter-form">
                                <tr class="c-table__row">
                                    <th class="c-table__cell c-table__cell--head">
                                        <select name="readiness" class="c-input" onchange="document.forms['filter-form'].submit()">
                                            <optgroup label="Filter by readiness">
                                                <option value="">ALL</option>
                                                <option value="ready_for_approval" {{ app('request')->input('readiness') === 'ready_for_approval' ? 'selected' : '' }}>Ready for approval</option>
                                                <option value="pay_out" {{ app('request')->input('readiness') === 'pay_out' ? 'selected' : '' }}>Pay out</option>
                                                <option value="paid_out" {{ app('request')->input('readiness') === 'paid_out' ? 'selected' : '' }}>Paid out</option>
                                            </optgroup>
                                        </select>
                                    </th>
                                    <th class="c-table__cell c-table__cell--head">
                                        <input class="c-input" type="text" name="owner" placeholder="Loan owner" value="{{app('request')->input('owner')}}">
                                    </th>
                                    <th class="c-table__cell c-table__cell--head">
                                        <input class="c-input" type="date" name="date_from" value="{{app('request')->input('date_from')}}" placeholder="From date">
                                    </th>

                                    <th class="c-table__cell c-table__cell--head">
                                        <select name="status" class="c-input" id="status"  onchange="document.forms['filter-form'].submit()">
                                            <option value="">ALL</option>
                                            @foreach(['new','processing','approved','rejected','unknown','completed','approval','waiting'] as $status)
                                                <option value="{{$status}}" @if(app('request')->input('status') == $status) selected="selected" @endif>{{ucfirst($status)}}</option>
                                            @endforeach
                                        </select>
                                    </th>
                                    <th class="c-table__cell c-table__cell--head">
                                        <select name="loan_type" id="" class="c-input" onchange="document.forms['filter-form'].submit()">
                                            <option value="">ALL</option>
                                            @foreach($loanTypes as $loanType)
                                                <option value="{{$loanType->id}}" @if(app('request')->input('loan_type') == $loanType->id) selected="selected" @endif>{{$loanType->name}}</option>
                                            @endforeach
                                        </select>

                                    </th>

                                    <th class="c-table__cell c-table__cell--head">
                                        <button value="filter" class="c-btn c-btn--small">Filter</button>
                                        <a title="Download CSV" href="{{ $downloadLink }}" target="_blank" class="btn-download">
                                            <i class="fa fa-file-csv"></i>
                                        </a>
                                    </th>
                                </tr>
                            </form>
                            </thead>

                            <tbody>
                            @foreach($loans as $loan)
                                <tr class="c-table__row">
                                    <td class="c-table__cell">
                                        {{$loan->amount()}}
                                        @if($loan->status == 'approved' && !$loan->paid_out)
                                            <a href="{{route('mainframe.loans.payments', $loan->id)}}"><small class="c-badge c-badge--warning c-badge--small">Pay out</small></a>
                                        @endif
                                        @if($loan->paid_out)
                                            <small class="c-badge c-badge--success c-badge--small">Paid Out</small>
                                        @endif

                                        @if($loan->status == 'new' && !$loan->owner->checkList() && $loan->required_documents_uploaded)
                                            <small class="c-badge c-badge--info c-badge--small">Ready for approval</small>
                                        @endif
                                    </td>
                                    <th class="c-table__cell">
                                        @if($loan->owner)
                                        {{$loan->owner->name}}<br /> {{$loan->owner->email}}
                                        @endif
                                    </th>
                                    <th class="c-table__cell">{{$loan->created_at}}</th>
                                    <td class="c-table__cell">
                                        <a class="c-badge c-badge--small c-badge--info" href="#">{{$loan->status}}</a>
                                    </td>
                                    <th class="c-table__cell">{{$loan->loanType ? $loan->loanType->name : ''}}</th>
                                    <td class="c-table__cell">
                                        <div class="c-dropdown dropdown">
                                            <a href="#" class="c-btn c-btn--info has-icon dropdown-toggle" id="dropdownMenuTable1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                More <i class="feather icon-chevron-down"></i>
                                            </a>

                                            <div class="c-dropdown__menu dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuTable1">
                                                <a class="c-dropdown__item dropdown-item" href="{{route('mainframe.loans.show', $loan->id)}}">View loan</a>
                                                @if(!$loan->paid_out)
                                                <a class="c-dropdown__item dropdown-item" href="{{route('mainframe.loans.payments', $loan->id)}}">Pay Out</a>
                                                @endif

                                                @if($loan->status !=='completed')
                                                <a class="c-dropdown__item dropdown-item" href="?deleteLoan={{$loan->id}}" onclick="return confirm('Please confirm that you want this loan deleted.\n\nThis action is not reversible. \n\nThe status of the will be marked rejected and all payment information deleted.')">Delete Loan</a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>

                        {{$loans->links()}}
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
