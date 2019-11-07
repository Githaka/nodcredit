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
                        <h4>{{ $accountUser->getName() }}: Synchronized Contacts ({{ $contacts->count() }})</h4>
                        <br>

                        @php
                            $activeContacts = $contacts->filterByActive();
                            $deletedContacts = $contacts->filterByTrashed();
                        @endphp

                        <h4 class="u-mb-small">Active Contacts ({{ $activeContacts->count() }})</h4>
                        <table class="c-table u-mb-medium">
                            <thead class="c-table__head">
                                <tr class="c-table__row">
                                    <th style="width: 70px;" class="c-table__cell c-table__cell--head">#</th>
                                    <th class="c-table__cell c-table__cell--head">Name</th>
                                    <th class="c-table__cell c-table__cell--head">Emails</th>
                                    <th class="c-table__cell c-table__cell--head">Phones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $counter = 1;
                                @endphp

                                @foreach($activeContacts->all() as $contact)
                                    <tr class="c-table__row">
                                        <th class="c-table__cell">{{ $counter++ }}.</th>
                                        <td class="c-table__cell">
                                            <div class="o-media">
                                                <div class="o-media__body">
                                                    <h6>{{ $contact->getName() }}</h6>

                                                    @if($contact->getStarred() )
                                                        <p><i class="feather icon-star"></i> Starred</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="c-table__cell">
                                            @if ($contact->getActiveEmails()->count())
                                                <p><b>Active</b></p>
                                                @foreach($contact->getActiveEmails() as $email)
                                                    <p><a href="mailto:{{ $email->email }}">{{ $email->email }}</a></p>
                                                @endforeach
                                                <br>
                                            @endif

                                            @if ($contact->getTrashedEmails()->count())
                                                <p><b>Deleted</b></p>
                                                @foreach($contact->getTrashedEmails() as $email)
                                                    <p><a href="mailto:{{ $email->email }}">{{ $email->email }}</a></p>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="c-table__cell">
                                            @if ($contact->getActivePhones()->count())
                                                <p><b>Active</b></p>
                                                @foreach($contact->getActivePhones() as $phone)
                                                    <p>{{ $phone->phone }}</p>
                                                @endforeach
                                                <br>
                                            @endif

                                            @if ($contact->getTrashedPhones()->count())
                                                <p><b>Deleted</b></p>
                                                @foreach($contact->getTrashedPhones() as $phone)
                                                    <p>{{ $phone->phone }}</p>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @if (! $activeContacts->count())
                                    <tr class="c-table__row">
                                        <th colspan="4" class="c-table__cell text-center">No records</th>
                                    </tr>
                                @endif

                            </tbody>
                        </table>

                        <h4 class="u-mb-small">Deleted Contacts ({{ $deletedContacts->count(0) }})</h4>
                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th style="width: 70px;" class="c-table__cell c-table__cell--head">#</th>
                                <th class="c-table__cell c-table__cell--head">Name</th>
                                <th class="c-table__cell c-table__cell--head">Emails</th>
                                <th class="c-table__cell c-table__cell--head">Phones</th>
                            </tr>
                            </thead>

                            <tbody>

                            @php
                                $counter = 1;
                            @endphp

                            @foreach($deletedContacts->all() as $contact)
                                <tr class="c-table__row">
                                    <th class="c-table__cell">{{ $counter++ }}.</th>
                                    <td class="c-table__cell">
                                        <div class="o-media">
                                            <div class="o-media__body">
                                                <h6>{{ $contact->getName() }}</h6>

                                                @if($contact->getStarred() )
                                                    <p><i class="feather icon-star"></i> Starred</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="c-table__cell">
                                        @foreach($contact->getEmails() as $email)
                                            <p><a href="mailto:{{ $email->email }}">{{ $email->email }}</a></p>
                                        @endforeach
                                    </td>
                                    <td class="c-table__cell">
                                        @foreach($contact->getPhones() as $phone)
                                            <p>{{ $phone->phone }}</p>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach

                            @if (! $deletedContacts->count())
                                <tr class="c-table__row">
                                    <th colspan="4" class="c-table__cell text-center">No records</th>
                                </tr>
                            @endif

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
