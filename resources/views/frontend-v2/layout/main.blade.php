<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="description" content="@yield('meta_description', 'Fast & Easy Loan with Low Interest Rate, Zero Collateral')">
	<meta name="keywords" content="@yield('meta_keywords', 'Zero Collateral, NodCredit, Cheap Loan, Instant Loan, Nigeria Instant loan, Nigeria school loan, Nigeria cheapest loan')">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>@yield('meta_title', 'Zero Collateral, Instant Loan - NodCredit')</title>

	<link rel="apple-touch-icon" sizes="144x144" href="/assets/images/favicons/apple-touch-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/assets/images/favicons/apple-touch-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/assets/images/favicons/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" href="/assets/images/favicons/apple-touch-icon.png">
	<link rel="shortcut icon" href="/assets/images/favicons/favicon.png">

	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<!-- Bootstrap -->
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.2/tiny-slider.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.2/min/tiny-slider.helper.ie8.js"></script>

	@yield('head')

	<link rel="stylesheet" href="/frontend/styles/styles.css">

	@includeWhen(\App::environment() === 'production', '_partials.ogatracker')

	@includeWhen(\App::environment() === 'production', '_partials.gtag')

</head>

<body class="page @yield('body_class')">

	<div id="frontend">
		@yield('body')

		@section('footer')
			@include('frontend-v2.partials.footer')
		@show

	</div>

	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.2/min/tiny-slider.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.1.1/js/mdb.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"></script>
	<script src="/frontend/js/script.js"></script>
	<script src="/frontend/js/frontend.js"></script>

	@yield('footer_scripts')

</body>

</html>