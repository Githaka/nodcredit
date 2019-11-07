<aside class="c-sidebar dark-bkg">
    <div class="c-sidebar__brand">
        <a href="{{route('account.home')}}"><img src="{{asset('dashboard/img/logo.svg')}}" alt="NodCredit"></a>
    </div>

    <!-- Scrollable -->
    <div class="c-sidebar__body">

        @if(Auth::user()->role == 'partner')
            <span class="c-sidebar__title">MENU</span>
        <ul class="c-sidebar__list">
            <li>
                <a class="c-sidebar__link" href="{{route('account.profile.invest')}}">
                    <i class="c-sidebar__icon feather icon-home"></i>Dashboard
                </a>
            </li>
            <li>
                <a class="c-sidebar__link" href="{{route('account.profile')}}">
                    <i class="c-sidebar__icon feather icon-user"></i>Profile
                </a>

            </li>

            <li>
                <a class="c-sidebar__link" href="{{route('user.change.password')}}">
                    <i class="c-sidebar__icon feather icon-lock"></i>Change Password
                </a>

            </li>
            {{-- <li>
                <a class="c-sidebar__link" href="{{route('account.profile.invest')}}"  style="color: #ff9000;">
                    <i class="c-sidebar__icon feather icon-briefcase"></i>Invest
                </a>
            </li> --}}

        </ul>
        @endif

        @if(in_array(Auth::user()->role, ['admin', 'user', 'support']))
            <span class="c-sidebar__title">MENU</span>
            <ul class="c-sidebar__list">
                <li>
                    <a class="c-sidebar__link" href="{{route('account.home')}}">
                        <i class="c-sidebar__icon feather icon-home"></i>Dashboard
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('account.loans')}}">
                        <i class="c-sidebar__icon feather icon-move"></i>My Loans
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('account.loans.repayment')}}">
                        <i class="c-sidebar__icon feather icon-calendar"></i>Loan Re-Payment
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('account.profile')}}">
                        <i class="c-sidebar__icon feather icon-user"></i>Profile
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('user.change.password')}}">
                        <i class="c-sidebar__icon feather icon-lock"></i>Change Password
                    </a>
                </li>
            </ul>
        @endif

        @if(Auth::user()->isAdmin())
            <a href="{{route('mainframe.dashboard')}}"> <span class="c-sidebar__title">ADMIN DASHBOARD</span></a>
            <ul class="c-sidebar__list">
                <li>
                    <a class="c-sidebar__link" href="{{route('mainframe.loans')}}">
                        <i class="c-sidebar__icon feather icon-layers"></i>Loans
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('mainframe.payments')}}">
                        <i class="c-sidebar__icon feather icon-layers"></i>Payments
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('mainframe.investments')}}">
                        <i class="c-sidebar__icon feather icon-layers"></i>Investments
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.accounts')}}">
                        <i class="c-sidebar__icon feather icon-users"></i>Accounts
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.accounts.banned')}}">
                        <i class="c-sidebar__icon feather icon-slash"></i>Blacklist
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.commons.loan.doc-type')}}">
                        <i class="c-sidebar__icon feather icon-book"></i>Loan Document Types
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.transactions')}}">
                        <i class="c-sidebar__icon feather icon-anchor"></i>Transactions
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.settings')}}">
                        <i class="c-sidebar__icon feather icon-anchor"></i>Settings
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.loan-ranges')}}">
                        <i class="c-sidebar__icon feather icon-anchor"></i>Manage Loan Ranges
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.score-config')}}">
                        <i class="c-sidebar__icon feather icon-anchor"></i>Manage Score Config
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.message-templates')}}">
                        <i class="c-sidebar__icon feather icon-anchor"></i>Message Templates
                    </a>
                </li>
            </ul>

        @elseif(Auth::user()->isSupport())

            <span class="c-sidebar__title">SUPPORT MENU</span>
            <ul class="c-sidebar__list">
                <li>
                    <a class="c-sidebar__link" href="{{route('mainframe.payments')}}">
                        <i class="c-sidebar__icon feather icon-layers"></i>Payments
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.accounts')}}">
                        <i class="c-sidebar__icon feather icon-users"></i>Accounts
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.accounts.banned')}}">
                        <i class="c-sidebar__icon feather icon-slash"></i>Blacklist
                    </a>
                </li>
                <li>
                    <a class="c-sidebar__link" href="{{route('admin.transactions')}}">
                        <i class="c-sidebar__icon feather icon-anchor"></i>Transactions
                    </a>
                </li>
            </ul>
        @endif

    </div>



    <a class="c-sidebar__footer" href="{{route('account.logout')}}">
        Logout <i class="c-sidebar__footer-icon feather icon-power"></i>
    </a>
</aside>
