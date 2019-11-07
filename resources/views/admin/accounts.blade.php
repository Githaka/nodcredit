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

            <div class="row">
                <div class="col-12">
                    <div class="c-table-responsive@wide">
                        <h4 class="u-mb-small">Accounts</h4>

                        <div class="u-mb-small">
                            <admin-accounts-investor-add></admin-accounts-investor-add>
                        </div>

                        <p class="u-mb-medium">We have {{$totalAccounts}} accounts in the system including you.</p>

                        <form action="" method="get" class="accounts-filter-form">
                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">
                                    <select name="role" class="c-input" onchange="document.querySelector('.accounts-filter-form').submit()">
                                        <optgroup label="Filter by Role">
                                            <option value="">ALL</option>
                                            <option value="user" {{ app('request')->input('role') === 'user' ? 'selected' : '' }}>Regular Customer</option>
                                            <option value="partner" {{ app('request')->input('role') === 'partner' ? 'selected' : '' }}>Investor</option>
                                            <option value="admin" {{ app('request')->input('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                                            <option value="support" {{ app('request')->input('role') === 'support' ? 'selected' : '' }}>Support</option>
                                        </optgroup>
                                    </select>
                                </th>
                                <th class="c-table__cell c-table__cell--head">
                                    <input type="text" class="c-input" name="q" placeholder="Keyword" value="{{app('request')->input('q')}}">
                                </th>

                                <th class="c-table__cell c-table__cell--head" colspan="6">
                                    <button value="filter" class="c-btn c-btn--small">Filter</button>
                                    <a title="Download CSV" href="{{ $downloadLink }}" target="_blank" class="btn-download">
                                        <i class="fa fa-file-csv"></i>
                                    </a>
                                </th>
                            </tr>
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Name</th>
                                <th class="c-table__cell c-table__cell--head">Email</th>
                                <th class="c-table__cell c-table__cell--head">Phone</th>
                                <th class="c-table__cell c-table__cell--head">BVN Phone</th>
                                <th class="c-table__cell c-table__cell--head">Gender</th>
                                <th class="c-table__cell c-table__cell--head">Birth Date</th>
                                <th class="c-table__cell c-table__cell--head">Loans</th>
                                <th class="c-table__cell c-table__cell--head">Action</th>
                            </tr>

                            </thead>

                            <tbody>
                            @foreach($accounts as $account)
                                <tr class="c-table__row">
                                    <td class="c-table__cell" style="white-space: normal">
                                        <div class="o-media">
                                            <div class="o-media__img u-mr-xsmall">
                                                <div class="c-avatar c-avatar--small">
                                                    <img class="c-avatar__img" src="{{$account->avatar_url}}" alt="{{$account->name}}">
                                                </div>
                                            </div>
                                            <div class="o-media__body">
                                                <h6>{{$account->name}}</h6>
                                                <p>
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

                                                @if($account->banned_at)
                                                    <p class="u-text-danger">Banned at {{ $account->banned_at->format('Y-m-d H:i') }}</p>

                                                    @if ($account->ban_reason)
                                                        <p class="u-text-danger">{{ $account->ban_reason }}</p>
                                                    @endif

                                                @endif

                                            </div>
                                        </div>
                                    </td>
                                    <td class="c-table__cell"><a href="{{route('admin.accounts.message', $account->id)}}">{{$account->email}}</a></td>
                                    <th class="c-table__cell">
                                        <a href="{{route('admin.accounts.message', $account->id)}}">{{$account->phone}}</a>
                                    </th>
                                    <td class="c-table__cell">{{ $account->bvn_phone }}</td>
                                    <td class="c-table__cell">{{$account->gender}}</td>
                                    <td class="c-table__cell">{{$account->dob}}</td>
                                    <td class="c-table__cell">
                                        <a class="c-badge c-badge--small c-badge--info" href="#">{{$account->applications_count}}</a>
                                    </td>
                                    <td class="c-table__cell">
                                        <div class="c-dropdown dropdown">
                                            <a href="#" class="c-btn c-btn--info has-icon dropdown-toggle" id="dropdownMenuTable1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                More <i class="feather icon-chevron-down"></i>
                                            </a>

                                            <div class="c-dropdown__menu dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuTable1">
                                                <a class="c-dropdown__item dropdown-item" href="{{route('admin.accounts.show', $account->id)}}">View Account</a>
                                                <a class="c-dropdown__item dropdown-item" href="{{route('admin.change.password', $account->id)}}">Change Password</a>
                                                <a class="c-dropdown__item dropdown-item" href="{{route('admin.accounts.shadow', $account->id)}}">Shadow</a>
                                                <a class="c-dropdown__item dropdown-item" href="{{route('admin.accounts.contacts', $account->id)}}">Contacts</a>
                                                <a class="c-dropdown__item dropdown-item" href="{{route('admin.accounts.locations', $account->id)}}">Locations</a>

                                                @if ($account->banned_at)
                                                    <a class="c-dropdown__item dropdown-item u-text-danger" href="{{route('admin.accounts.unban', $account->id)}}">Unban Account</a>
                                                @else
                                                    <admin-accounts-ban-button class="c-dropdown__item dropdown-item" :id="'{{ $account->id }}'">Ban Account</admin-accounts-ban-button>
                                                @endif

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                        </form>

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
