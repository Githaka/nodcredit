<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Zero Collateral, Instant Loan - NodCredit</title>
	<meta name="description" content="Fast & Easy Loan with Low Interest Rate, Zero Collateral">
	<meta name="keywords" content="Zero Collateral, NodCredit, Cheap Loan, Instant Loan, Nigeria Instant loan, Nigeria school loan, Nigeria cheapest loan">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="apple-touch-icon" sizes="144x144" href="assets/images/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="114x114" href="assets/images/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="assets/images/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" href="assets/images/favicons/apple-touch-icon.png">
    <link rel="shortcut icon" href="assets/images/favicons/favicon.png">
	<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/style.css">
	
	<link rel="stylesheet" href="assets/plugins/animate.css">
	<link rel="stylesheet" href="assets/plugins/line-icons/line-icons.css">
	<link rel="stylesheet" href="assets/plugins/font-awesome/version5/css/fontawesome-all.min.css">
	<link rel="stylesheet" href="assets/plugins/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="assets/plugins/owl-carousel/owl-carousel/owl.carousel.css">
	<link rel="stylesheet" href="assets/plugins/layer-slider/layerslider/css/layerslider.css">
	
	<link rel="stylesheet" href="assets/plugins/sky-forms-pro/skyforms/css/sky-forms.css">
	<link rel="stylesheet" href="assets/plugins/sky-forms-pro/skyforms/custom/custom-sky-forms.css">

	<link rel="stylesheet" href="assets/css/main.css">
	<link rel="stylesheet" href="assets/css/style-resize.css">

	@includeWhen(\App::environment() === 'production', '_partials.ogatracker')

	@includeWhen(\App::environment() === 'production', '_partials.gtag')

</head>

<body class="header-fixed header-fixed-space">
	<div class="wrapper">
		<div class="header-v6 header-classic-white header-sticky nod-header">
			<div class="navbar mega-menu" role="navigation">
				<div class="container">
					<div class="menu-container">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>

						<div class="navbar-brand">
							<a href="/">
								<img src="assets/images/logo-white.svg" alt="Logo">
							</a>
						</div>
					</div>

					<div class="collapse navbar-collapse navbar-responsive-collapse">
						<div class="menu-container">
							<ul class="nav navbar-nav">
								<li class=""><a href="/">Home</a></li>
								<li class=""><a href="{{route('get-loan')}}">Get a Loan</a></li>
								<li class=""><a href="/invest">Invest</a></li>
								<li class=""><a href="mailto:info@nodcredit.com" target="_blank">Contact us</a></li>
								@if(Auth::user())
								<li class=""><a href="{{route('account.home')}}" class="get-started-menu">Account</a></li>
								@else
									<li class=""><a href="{{route('login')}}" class="get-started-menu">Sign in</a></li>
								@endif
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
