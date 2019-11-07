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
                        <h4>Blacklist</h4>


                        <table class="c-table">
                            <thead class="c-table__head">

                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Account</th>
                                <th class="c-table__cell c-table__cell--head">Ban reason</th>
                                <th class="c-table__cell c-table__cell--head">Banned at</th>
                                <th class="c-table__cell c-table__cell--head">Actions</th>
                            </tr>

                            </thead>

                            <tbody>
                            @foreach($accounts as $account)
                                <tr class="c-table__row">
                                    <td class="c-table__cell">
                                        <div class="o-media">
                                            <div class="o-media__img u-mr-xsmall">
                                                <div class="c-avatar c-avatar--small">
                                                    <img class="c-avatar__img" src="{{$account->avatar_url}}" alt="{{$account->name}}">
                                                </div>
                                            </div>
                                            <div class="o-media__body">
                                                <h6>{{$account->name}}</h6>
                                                <p class="u-mb-small">
                                                    @if($account->isUser())
                                                        Regular Customer
                                                    @elseif($account->isPartner())
                                                        Investor
                                                    @elseif ($account->isAdmin())
                                                        Administrator
                                                    @elseif ($account->isSupport())
                                                        Support
                                                    @endif
                                                </p>

                                                <p><a href="{{route('admin.accounts.message', $account->id)}}">{{$account->email}}</a></p>
                                                <p class="u-mb-small"><a href="{{route('admin.accounts.message', $account->id)}}">{{$account->phone}}</a></p>
                                                <p>BVN: {{ $account->bvn }}</p>
                                                <p>BVN Phone: {{ $account->bvn_phone }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="c-table__cell" style="white-space: normal">{{$account->ban_reason}}</td>
                                    <td class="c-table__cell">{{$account->banned_at}}</td>
                                    <td class="c-table__cell">
                                        <a class="c-btn c-btn--small" href="{{ route('admin.accounts.unban', ['id' => $account->id]) }}">Unban</a>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>

                        {{$accounts->links()}}
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
