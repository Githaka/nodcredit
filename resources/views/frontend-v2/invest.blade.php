@extends('frontend-v2.layout.main')

@section('body')

	<header>
		<div class="container">
			<div class="navigation">
				@include('frontend-v2.partials.header-logo')
				@include('frontend-v2.partials.invest-navigation')
			</div>
		</div>
	</header>

	<section class="herocover">
		<div class="container">
			<div class="hero">
				<div class="leftSide">
					<div class="content invest">
						<h2 class="bannerText">Make Secure Investment on NodCredit</h2>
						<p>Your money can work for you while you sit back and chill. We offer market leading interest rates, on very flexible maturity tenors. <span>Earn 20 - 30% p.a on your money.</span> </p>
					</div>
					<a href="{{ route('frontend.invest.start') }}" class="largeButton investbtn btn-fill-md">Start Investing</a>
				</div>
				<div class="rightSide">
					<img src="/frontend/images/investbg.png" alt="Nod Credit Hero image">
				</div>

			</div>

		</div>
	</section>

	<section class="invest">
		<div class="container">
			<div class="how-it-works">
				<div class="leftImg">
					<img src="/frontend/images/invest-how-it-works.png" alt="how it works image">
				</div>
				<div class="rightContent ">
					<h3>How It Works</h3>
					<p>Get your loan in just 3 easy steps.</p>
					<div class="steps">
						<div class="step1">
							<div class="row">
								<div class="col-2">
									<div class="number">1</div>
								</div>
								<div class="col-10">
									<h6>Sign Up</h6>
									<p>Sign up on NodCredit, using your BVN and other matching details. Confirm your registration.</p>
								</div>
							</div>
						</div>
						<div class="step2">
							<div class="row">
								<div class="col-2">
									<div class="number">2</div>
								</div>
								<div class="col-10">
									<h6>Login & Invest</h6>
									<p>Access your account and click on the invest menu link, enter your preferred interest rates & tenor.</p>
								</div>
							</div>
						</div>
						<div class="step3">
							<div class="row">
								<div class="col-2">
									<div class="number">3</div>
								</div>
								<div class="col-10">
									<h6>Relax and Watch your money grow</h6>
									<p>Make investment deposit via card or bank deposit. Sit back and watch your money grow.</p>
								</div>
							</div>
						</div>
						<div class="topBottom">
							<a href="{{ route('frontend.invest.start') }}" class="largeButton btn-fill-md">Apply Now</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	@if ($faqItems->count())
	<section class="faq">
		<div class="container">
			<h3>Got Questions about Investing?</h3>
			<p>Not to worry, we’ve got answers.</p>
			<div class="faqContent">

				<div class="col-12 col-md-9 m-auto">

					<div class=" accordion" id="accordionExample">
						<div class="row mt-3">
							@foreach($faqItems as $item)
								<div class="col-sm-6">
									<div class="card">
										<div class="card-header" id="headingOne">
											<h2 class="mb-0">
												<button type="button" class="btn btn-link collapsed" data-toggle="collapse" data-target="#faq-{{ $item->id }}">
													<i class="fa fa-plus"></i> {{ $item->title }}
												</button>
											</h2>
										</div>

										<div id="faq-{{ $item->id }}" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
											<div class="card-body">
												<p>{{ $item->text }}</p>
											</div>
										</div>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	@endif

	<section class="finalnote">
		<div class="container">
			<div class="fnBg">
				<div class="content">
					<h3>Want to talk to one of our representative?</h3>
					<div class="fnBtn">
						<a href="" class="largeButton btn-fill-md">Call Micheal</a>
					</div>

				</div>
			</div>
		</div>
	</section>
@endsection