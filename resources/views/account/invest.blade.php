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

                <account-investments></account-investments>

    @include('dashboard.includes.footer')

    </div>
    </main>
    </div>


    <!-- Modal -->
    <div class="c-modal modal fade" id="invest-modal" tabindex="-1" role="dialog" aria-labelledby="invest-modal">
        <div class="c-modal__dialog modal-dialog" role="document">
            <div class="c-modal__content">
                <div class="c-modal__body">
                    <span class="c-modal__close" data-dismiss="modal" aria-label="Close">
                        <i class="feather icon-x"></i>
                    </span>
                    <h3 class="u-mb-small">Calculate your returns</h3>

                    <div class="u-mb-medium">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="u-pv-small result-card">
                                    <p>Total Returns at <span id="invest-percent"></span>% Interest</p>
                                    <h3>&#8358;<span id="invest-capital"></span></h3>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="u-pv-small result-card">
                                    <p>Total Net Profit</p>
                                    <h3>&#8358;<span id="invest-netprofit"></span></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="u-mb-medium">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Investment Amount</p>
                                <input type="text" id="amount" class="c-input c-input--success investment-form"
                                    placeholder={{number_format(get_setting('investment_min_amount'))}}>
                                <p class="small-text">Minimum Deposit:
                                    NGN{{number_format(get_setting('investment_min_amount'))}}</p>
                            </div>

                            <div class="col-md-6">
                                <p>Tenor of investment?</p>
                                <div class="c-select">
                                    <select class="c-select__input investment-form" name="investmentType"
                                        id="investment-type">
                                        @foreach($investmentConfig as $item)
                                        <option value="{{$item->value}}" selected="selected">{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <p class="u-mb-medium u-pt-medium">
                                    <button class="c-btn btn-primary c-btn--large c-btn--fullwidth" id="link-card-btn"
                                        data-action="invest">Make
                                        Investment</button>
                                </p>
                                <p class="u-mb-medium" style="text-align: center;">
                                    Secure online payment is powered by 
                                    <a href="https://paystack.com/" target="_blank">PayStack</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- // .c-modal__content -->
        </div><!-- // .c-modal__dialog -->
    </div><!-- // .c-modal -->



    <!-- Main JavaScript -->
    <script>
        Number.prototype.roundUp = function (places) {
            return +(Math.round(this + "e+" + places) + "e-" + places);
        }

        function formatMoney(n, c, d, t) {
            var c = isNaN(c = Math.abs(c)) ? 2 : c,
                d = d == undefined ? "." : d,
                t = t == undefined ? "," : t,
                s = n < 0 ? "-" : "",
                i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
                j = (j = i.length) > 3 ? j % 3 : 0;

            return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math
                .abs(
                    n - i).toFixed(c).slice(2) : "");
        };
    </script>
    @include('dashboard.includes.account-footer')

</body>

</html>
