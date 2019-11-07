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
                    <div class="c-table-responsive">
                        <h4>Transactions</h4>

                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Date</th>
                                <th class="c-table__cell c-table__cell--head">Amount</th>
                                <th class="c-table__cell c-table__cell--head">Status</th>
                                <th class="c-table__cell c-table__cell--head">Performed by</th>
                                <th class="c-table__cell c-table__cell--head">User</th>
                                <th class="c-table__cell c-table__cell--head">Response</th>
                                <th class="c-table__cell c-table__cell--head">Type</th>
                                <th class="c-table__cell c-table__cell--head">Card</th>
                                <th class="c-table__cell c-table__cell--head">Pay for</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($transactions as $transaction)
                                <tr class="c-table__row">
                                    <td class="c-table__cell">{{$transaction->created_at}}</td>
                                    <td class="c-table__cell">{{$transaction->getAmount()}}</td>
                                    <td class="c-table__cell">{{$transaction->status}}</td>
                                    <td class="c-table__cell">
                                        @if ($transaction->owner)
                                            {{ $transaction->owner->name }}<br>
                                            {{ $transaction->owner->email }}
                                        @else
                                            System
                                    @endif
                                    <td class="c-table__cell">
                                        @if ($transaction->user)
                                            {{ $transaction->user->name }}<br>
                                            {{ $transaction->user->email }}
                                        @else
                                            No data
                                        @endif
                                    </td>
                                    <td class="c-table__cell">{{$transaction->response_message}}</td>
                                    <td class="c-table__cell">{{$transaction->trans_type}}</td>
                                    <td class="c-table__cell">
                                        @if ($transaction->card)
                                            {{ $transaction->card->card_number }},
                                            {{ $transaction->card->exp_month }}/{{ $transaction->card->exp_year }}
                                        @else
                                            No data
                                        @endif
                                    </td>
                                    <td class="c-table__cell">{{ $transaction->pay_for }}</td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>

                        {{$transactions->links()}}
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
