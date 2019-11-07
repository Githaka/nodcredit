@include('dashboard.includes.header')

<body>

<div class="o-page" id="app">
    <div class="o-page__sidebar js-page-sidebar">
        @include('dashboard.includes.sidebar')
    </div>

    <main class="o-page__content">
        @include('dashboard.includes.header-nav')

        <div class="container">

            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="c-card">
                                <span class="c-icon c-icon--info u-mb-small">
                                    <i class="feather icon-activity"></i>
                                </span>

                                <h3 class="c-text--subtitle">Total Paid Back</h3>
                                <h1 class="u-mb-small">&#8358;{{number_format($totalPaid,2)}}</h1>
                                <h3 class="c-text--subtitle">Total Paid Out</h3>
                                <h1>&#8358;{{number_format($totalPaidOut,2)}}</h1>
                                <h3 class="c-text--subtitle">Total disbursed + rollover</h3>
                                <h2>&#8358;{{number_format($totalDisbursedPlusRollover,2)}}</h2>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="c-card">
                                <span class="c-icon c-icon--info u-mb-small">
                                    <i class="feather icon-activity"></i>
                                </span>
                                <div class="row">
                                <div class="col-12">
                                    <h3 class="c-text--subtitle">Total Number of Loan Disbursed</h3>
                                    <h1 class="u-mb-small">{{ $totalDisbursedLoans }}</h1>
                                </div>
                                <div class="col-6">
                                    <h3 class="c-text--subtitle">Total Completed</h3>
                                    <h1 class="u-mb-small">{{ $totalCompletedLoans }}</h1>
                                </div>
                                <div class="col-6">
                                    <h3 class="c-text--subtitle">Total Rejected</h3>
                                    <h1 class="u-mb-small">{{ $totalRejectedLoans }}</h1>
                                </div>
                                </div>
                            </div>
                        </div>

                        </div>

                        @if(Auth::user()->role == 'admin')
{{--
                            <div class="col-md-6">
                                <div class="c-card">


                                    <h3 class="c-text--subtitle">Loan Information</h3>
                                   <table class="c-table c-table__row--info">
                                        <tbody>
                                        @foreach(['new','processing','approved','rejected','unknown','completed','approval'] as $item)
                                         <tr class="c-table__row" onclick="document.location='{{route('mainframe.loans')}}?status={{$item}}'" style="cursor: pointer;">
                                             <td class="c-table__cell">{{$item}}</td>
                                             <td class="c-table__cell">
                                                {{$loanStatus($item)}}
                                             </td>
                                         </tr>
                                         @endforeach
                                        </tbody>
                                   </table>
                                </div>
                            </div>
--}}
                        @endif
                    </div>
                </div>

            </div>

        <div class="container">
            <admin-disbursed-and-repayment-chart></admin-disbursed-and-repayment-chart>

            <admin-customers-charts></admin-customers-charts>
        </div>


            @include('dashboard.includes.footer')

        </div>
    </main>
</div>

<!-- Main JavaScript -->
@include('dashboard.includes.account-footer')
</body>

</html>
