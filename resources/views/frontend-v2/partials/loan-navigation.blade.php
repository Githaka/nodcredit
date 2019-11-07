<nav>
    <div class="nav-mobile">
        <a id="nav-toggle" href="#"><span></span></a>
    </div>

    <ul class="nav-list">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('frontend.invest') }}">Become an Investor</a>
        </li>
        <li class="nav-item" id="howitworks">
            <a class="nav-link" href="{{ route('frontend.home') }}#howitworks">How it Works</a>
        </li>
        <li class="nav-item" id="faq">
            <a class="nav-link" href="{{ route('frontend.home') }}#faq">FAQ</a>
        </li>

        @if(! auth()->user())
            <li class="nav-item">
                <a class="nav-link" href="{{ route('auth.login') }}">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn-outline-md" href="{{ route('frontend.loan.start') }}">Get Started</a>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link btn-outline-md" href="{{ route('account.home') }}">Account</a>
            </li>
        @endif

    </ul>
</nav>
