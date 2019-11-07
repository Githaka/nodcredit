<header class="c-navbar u-mb-medium nod-bkg">
    <button class="c-sidebar-toggle js-sidebar-toggle">
        <i class="feather icon-align-left"></i>
    </button>

    @if(Auth::user()->role == 'admin')
        <h2 class="c-navbar__title">Admin Console</h2>
    @else
        <h2 class="c-navbar__title">My Account</h2>
    @endif

    <a href="{{route('account.profile.invest')}}" class="c-btn c-btn--success nav-btn">&#8358;{{number_format(Auth::user()->balance)}}</a>

    @if(session()->has('shadowedBy'))
        <a href="{{route('admin.accounts.shadow.switch', session('shadowedBy'))}}" class="c-btn c-btn--danger nav-btn">Switch to admin</a>
    @endif


    <div class="c-dropdown dropdown">
        <div class="c-avatar c-avatar--xsmall dropdown-toggle" id="dropdownMenuAvatar" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" role="button">
            <img class="c-avatar__img" src="http://via.placeholder.com/350x150" alt="{{Auth::user()->name}}">
        </div>

        <div class="c-dropdown__menu has-arrow dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuAvatar">
            <a class="c-dropdown__item dropdown-item" href="{{route('account.profile')}}">Edit Profile</a>
            <a class="c-dropdown__item dropdown-item" href="@if(Auth::user()->role == 'admin'){{route('admin.change.password', Auth::user()->id)}} @else {{route('user.change.password')}} @endif">Change Password</a>
            <a class="c-dropdown__item dropdown-item" href="{{route('account.logout')}}">Log out</a>
        </div>
    </div>

    @if(session('success'))
    <div class="ui-alert ui-alert-success">
       {{session('success')}}
    </div>
    @endif

    @if(session('error'))
        <div class="ui-alert ui-alert-danger">
            {{session('error')}}
        </div>
    @endif
</header>
