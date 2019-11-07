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
                        <h4  class="u-mb-medium">Manage Score Config</h4>
                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Name</th>
                                <th class="c-table__cell c-table__cell--head">Score</th>
                                <th class="c-table__cell c-table__cell--head">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($scoreConfigs as $scoreConfig)
                                @if(!$scoreConfig->frequencies)
                                    @include('admin.score-config-no-frequencies')
                                @else
                                    @include('admin.score-config-frequencies')
                                @endif
                            @endforeach


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('dashboard.includes.footer')

        </div>
    </main>
</div>



</body>
</html>
