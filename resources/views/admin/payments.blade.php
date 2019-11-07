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
                        <h4>Loan Payments</h4>
                        <p class="element-box-content u-m-medium"></p>
                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head" colspan="7">
                                    <form action="" method="get" id="filter-form" name="filter-form" class="payments-filter">
                                        <div class="row">
                                            <div class="col-lg-2 u-mb-xsmall">
                                                <div class="c-select">
                                                    <select class="c-select__input payments-filter-due-in" name="due_in">
                                                        <option value="">Filter by due in...</option>

                                                        @foreach(range(0, 10) as $d)
                                                            @if (app('request')->input('due_in') !== null)
                                                                <option value="{{$d}}" {{ app('request')->input('due_in') == $d ? 'selected' : '' }}>
                                                                    {{$d  > 0 ? $d .' days': 'today'}}
                                                                </option>
                                                            @else
                                                                <option value="{{$d}}">{{$d  > 0 ? $d .' days': 'today'}}</option>
                                                            @endif

                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 u-mb-xsmall">
                                                <div class="c-field">
                                                    <input class="c-input payments-filter-overdue"  name="overdue_past"
                                                           value="{{app('request')->input('overdue_past')}}" type="number"
                                                           placeholder="Overdue past X days" min="1">
                                                </div>
                                            </div>

                                            <div class="col-lg-3 u-mb-xsmall">
                                                <div class="c-field">
                                                    <input class="c-input" name="q" value="{{app('request')->input('q')}}" type="text" id="input1" placeholder="Search by customer info">
                                                </div>
                                            </div>
                                            <div class="col-lg-2 u-mb-xsmall">
                                                <div class="c-field">
                                                    <button class="c-btn c-btn--link c-btn--small">Filter</button>
                                                    <a title="Download CSV" href="{{ $downloadLink }}" target="_blank" class="btn-download">
                                                        <i class="fa fa-file-csv"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </th>
                            </tr>
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Amount</th>
                                <th class="c-table__cell c-table__cell--head">Interest</th>
                                <th class="c-table__cell c-table__cell--head">Owner</th>
                                <th class="c-table__cell c-table__cell--head">Due Date</th>
                                <th class="c-table__cell c-table__cell--head">Status</th>
                                <th class="c-table__cell c-table__cell--head">Month</th>
                                <th class="c-table__cell c-table__cell--head">Action</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($payments as $payment)
                                <tr class="c-table__row {{$payment->status === 'paid' ? 'c-table__row--success': ''}}">
                                    <td class="c-table__cell">
                                       NGN{{number_format($payment->amount,2)}}
                                    </td>
                                    <td class="c-table__cell">
                                        NGN{{number_format($payment->interest,2)}}
                                    </td>
                                    <th class="c-table__cell">
                                            <a href="{{route('admin.accounts.message', $payment->loan->owner->id)}}">{{$payment->loan->owner->name}}<br /> {{$payment->loan->owner->email}}</a>
                                    </th>
                                    <th class="c-table__cell">{{$payment->dueDate()}}</th>
                                    <td class="c-table__cell">
                                        <a class="c-badge c-badge--small c-badge--info" href="{{app('request')->fullUrlWithQuery(['status' => $payment->status])}}">{{$payment->status}}</a>
                                    </td>
                                    <th class="c-table__cell">{{$payment->payment_month}}</th>
                                    <th class="c-table__cell">
                                        <div class="c-dropdown dropdown">
                                            <a href="#" class="c-btn c-btn--info has-icon dropdown-toggle" id="dropdownMenuTable{{$payment->id}}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                More <i class="feather icon-chevron-down"></i>
                                            </a>

                                            <div class="c-dropdown__menu dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuTable{{$payment->id}}">
                                                <a class="c-dropdown__item dropdown-item" href="{{route('mainframe.payments.show', $payment->id)}}">View Payment</a>

                                                @if(auth()->user()->isAdmin())

                                                    @if($payment->status !=='paid')
                                                        <a class="c-dropdown__item dropdown-item" href="?markAsPaid={{$payment->id}}" onclick="return confirm('You are about to mark this payment as paid, make sure you have received the fund before performing this action. \n\nClick ok to continue or Cancel to abort.')">Mark as Paid</a>
                                                        <a class="c-dropdown__item dropdown-item" href="?billPayment={{$payment->id}}" onclick="return confirm('Click OK to charge the customer`s Linked Debit Card for this payment.')">Bill Customer</a>
                                                    @endif

                                                    @if($payment->isOverdue() && !$payment->loan->paid_out)
                                                        <a class="c-dropdown__item dropdown-item" href="?removePayment={{$payment->id}}" onclick="return confirm('Click Ok to confirm your action.')">Delete</a>
                                                    @endif

                                                @endif

                                                <a class="c-dropdown__item dropdown-item" href="{{route('admin.accounts.shadow', $payment->loan->owner->id)}}">Shadow</a>

                                            </div>
                                        </div>
                                    </th>
                                </tr>

                                @if($payment->failedLog->count() && $payment->status !== 'paid' )
                                    <tr class="c-table__row">
                                        <td colspan="7">
                                            <ul>
                                                <li style="color: red; font-size: 12px;">**Loan was paid out on: {{$payment->loan->paid_out}}**</li>
                                                @foreach($payment->failedLog as $failedInfo)
                                                    <li>{{$failedInfo->res}} <small>{{$failedInfo->created_at}}</small></li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            </tbody>
                        </table>

                        {{$payments->links()}}
                    </div>
                </div>
            </div>

            @include('dashboard.includes.footer')

        </div>
    </main>
</div>

<script>
    window.document.addEventListener('DOMContentLoaded', function () {

        var $paymentsFilter = $('.payments-filter');

        $('.payments-filter-due-in').on('change', function () {

            $('.payments-filter-overdue').val('');

            $paymentsFilter.submit();
        });

        $('.payments-filter-overdue').on('change', function () {

            $('.payments-filter-due-in').val('');

            $paymentsFilter.submit();
        });

    });
</script>

<!-- Main JavaScript -->
@include('dashboard.includes.account-footer')

</body>
</html>
