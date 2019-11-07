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
                        <h4  class="u-mb-medium">Manage Loan Ranges</h4>

                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Min</th>
                                <th class="c-table__cell c-table__cell--head">Max</th>
                                <th class="c-table__cell c-table__cell--head">Min Month</th>
                                <th class="c-table__cell c-table__cell--head">Max Month</th>
                                <th class="c-table__cell c-table__cell--head">Min Score</th>
                                <th class="c-table__cell c-table__cell--head">Max Score</th>
                                <th class="c-table__cell c-table__cell--head">Action</th>
                            </tr>
                            </thead>

                            <tbody>

                                @foreach($settings as $setting)
                                    <form action="{{route('admin.loan-range.store')}}" method="post">
                                        {!! csrf_field() !!}

                                    <tr class="c-table__row">
                                        <td class="c-table__cell">
                                            <input type="hidden" name="id" value="{{$setting->id}}">
                                            <input type="text" class="c-input" value="{{number_format($setting->min,2)}}" name="min">
                                        </td>
                                        <td class="c-table__cell">
                                            <input type="text" class="c-input" value="{{number_format($setting->max,2)}}" name="max">
                                        </td>
                                        <td class="c-table__cell">
                                            <input type="text" class="c-input" value="{{$setting->min_month}}" name="min_month">
                                        </td>
                                        <td class="c-table__cell">
                                            <input type="text" class="c-input" value="{{$setting->max_month}}" name="max_month">
                                        </td>

                                        <td class="c-table__cell">
                                            <input type="text" class="c-input" value="{{$setting->min_score}}" name="min_score">
                                        </td>

                                        <td class="c-table__cell">
                                            <input type="text" class="c-input" value="{{$setting->max_score}}" name="max_score">
                                        </td>
                                        <td class="c-table__cell">
                                            <button type="submit" class="c-btn c-btn--primary">Save</button>
                                        </td>
                                    </tr>
                            </form>
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
