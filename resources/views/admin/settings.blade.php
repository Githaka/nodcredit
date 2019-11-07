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
                        <h4  class="u-mb-medium">Settings</h4>

                        <a class="c-btn c-btn--primary" href="{{route('admin.loan-ranges')}}">Manage Loan Ranges</a>
                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Setting Name</th>
                                <th class="c-table__cell c-table__cell--head">Value</th>
                            </tr>
                            </thead>

                            <tbody>
                            <form action="{{route('admin.settings.store')}}" method="post">
                                {!! csrf_field() !!}
                                @foreach($settings as $setting)
                                    <tr class="c-table__row">
                                        <td class="c-table__cell" style="white-space: normal">
                                            <p>{{ucfirst(str_ireplace('_', ' ', $setting->k))}}</p>
                                            <small>{{ $setting->description }}</small>
                                        </td>
                                        <td class="c-table__cell" style="width: 20%;"><input type="text" class="c-input" value="{{$setting->v}}" name="{{$setting->k}}"></td>
                                    </tr>
                                @endforeach
                                <tr class="c-table__row">
                                    <td class="c-table__cell">
                                        <input type="password" autocomplete="off" class="c-input c-input--success" name="password" placeholder="Enter your password to save settings">
                                    </td>
                                    <td class="c-table__cell">
                                        <button type="submit" class="c-btn c-btn--primary">Save</button>
                                    </td>
                                </tr>
                            </form>
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
