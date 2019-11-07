@extends('account.layout.main')

@section('content-container-body')
    <div class="row">
        <div class="col-12">
            <div class="c-table-responsive@wide">
                <h4>Loan Applications</h4>

                <p class="u-mb-medium">Below is a list of all your loan applications.

                    @if ($accountUser->canApplyForLoan())
                        If you want to request for a new loan <a href="{{route('account.profile.apply')}}">click here</a>
                    @endif

                </p>

                <table class="c-table">
                    <thead class="c-table__head">
                    <tr class="c-table__row">
                        <th class="c-table__cell c-table__cell--head">Loan Amount</th>
                        <th class="c-table__cell c-table__cell--head">Application Date</th>
                        <th class="c-table__cell c-table__cell--head">Tenor & Interest</th>
                        <th class="c-table__cell c-table__cell--head">Status</th>
                        <th class="c-table__cell c-table__cell--head">Type</th>
                        <th class="c-table__cell c-table__cell--head">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($loans as $loan)
                        <tr class="c-table__row">
                            <td class="c-table__cell">{{$loan->amount()}}</td>
                            <th class="c-table__cell">{{$loan->created_at}}</th>
                            <td class="c-table__cell">{{$loan->tenor}} months</td>
                            <td class="c-table__cell">
                                <a class="c-badge c-badge--small c-badge--info" href="#">{{$loan->status}}</a>
                            </td>
                            <th class="c-table__cell">{{$loan->loanType ? $loan->loanType->name : ''}}</th>

                            <td class="c-table__cell">
                                <div class="c-dropdown dropdown">
                                    <a href="#" class="c-btn c-btn--info has-icon dropdown-toggle" id="dropdownMenuTable1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        View Actions <i class="feather icon-chevron-down"></i>
                                    </a>

                                    <div class="c-dropdown__menu dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuTable1">
                                        <a class="c-dropdown__item dropdown-item" href="{{route('account.loans.show', $loan->id)}}">View Info</a>
                                        <a class="c-dropdown__item dropdown-item" href="#">Payments</a>
                                        @if($loan->status === 'new')
                                            <a class="c-dropdown__item dropdown-item" href="?removeLoan={{$loan->id}}">Delete</a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection