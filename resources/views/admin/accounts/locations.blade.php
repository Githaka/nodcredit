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
                        <h4>{{ $accountUser->getName() }}: Locations</h4>
                        <br>

                        <table class="c-table">
                            <thead class="c-table__head">
                                <tr class="c-table__row">
                                    <th class="c-table__cell c-table__cell--head">#</th>
                                    <th class="c-table__cell c-table__cell--head">Date / Coordinates</th>
                                    <th class="c-table__cell c-table__cell--head">Addresses</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($locations->all() as $key => $location)
                                    <tr class="c-table__row">
                                        <th class="c-table__cell">{{ $key+1 }}.</th>
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

<!-- Main JavaScript -->
@include('dashboard.includes.account-footer')

</body>
</html>
