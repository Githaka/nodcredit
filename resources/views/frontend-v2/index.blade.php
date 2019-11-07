@extends('frontend-v2.layout.main')

@section('body')
	<header>
		<div class="container">
			<div class="navigation">
				@include('frontend-v2.partials.header-logo')
				@include('frontend-v2.partials.loan-navigation')
			</div>
		</div>
	</header>

	<section class="herocover">
		<div class="container">
			<div class="hero">
				<div class="leftSide">
					<div class="content">
						<h2 class="bannerText">Fast and light loan Up to N50,000</h2>
						<p>Need some fast cash, We don't mind about your past, just the future. Get started with our loan application and secure your future.</p>
					</div>
					<a href="{{ route('frontend.loan.start') }}" class="largeButton btn-fill-md">Apply Now</a>
				</div>
				<div class="rightSide">
					<img src="/frontend/images/bannerbg.png" alt="Nod Credit Hero image">
				</div>
			</div>
			<div class="uniqueSellingPoint">
				<div class="usp">
					<div class="info">
						<div class="row">

							<div class="col-12 col-sm-4">
								<div class="row">
									<div class="col-2 usp-icon"><img src="/frontend/images/save.svg" alt="saving icon"></div>
									<div class="col-10 uspText">
										<p>Upto ₦50,000 <span>High Range Loan</span></p>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-4">
								<div class="row">
									<div class="col-2 usp-icon"><img src="/frontend/images/interest.svg" alt="saving icon"></div>
									<div class="col-10 uspText">
										<p>Best rate in the market <span>Competitive Interest</span></p>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-4">
								<div class="row">
									<div class="col-2 usp-icon"><img src="/frontend/images/minutes.svg" alt="saving icon"></div>
									<div class="col-10 uspText">
										<p>Within minutes <span>Fast and Easy Process</span></p>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>

			<div class="loancoverage">
				<h3>What our loan can cover</h3>
				<p>A personal loan through NodCredit can help you meet your financial goals.</p>

				<div class="covers">
					<div class="card-1 nodCard">
						<div><img src="/frontend/images/travel.svg" alt=""></div>
						<div>
							<h6>Travel <br>Support</h6>
						</div>
						<div>
							<p>Travel with ease, knowing we got your back through the journey. Pamper yourself and pay with ease. Travel Support</p>
						</div>
					</div>

					<div class="card-1 nodCard">
						<div><img src="/frontend/images/home.svg" alt=""></div>
						<div>
							<h6>Cover Home <br>Expenses</h6>
						</div>
						<div>
							<p>Pay for an unexpected home expense or major household purchase in fixed installments instead of waiting.</p>
						</div>
					</div>

					<div class="card-1 nodCard">
						<div><img src="/frontend/images/salary.svg" alt=""></div>
						<div>
							<h6>Supplement <br>your salary</h6>
						</div>
						<div>
							<p>Don't stretch your monthly keep any further let us get you that fiscal backup you need to make it through all year round.</p>
						</div>
					</div>

					<div class="card-1 nodCard">
						<div><img src="/frontend/images/health.svg" alt=""></div>
						<div>
							<h6>Get Medical<br>Expenses</h6>
						</div>
						<div>
							<p>Don't get stuck when it's a matter of life. Every second counts. Let us stand by you in cases of emergency.</p>
						</div>
					</div>
				</div>


			</div>
		</div>
	</section>

	<section>
		<div class="container">
			<div class="how-it-works">

				<div class="leftImg">
					<img src="/frontend/images/how-it-work.png" alt="how it works image">
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
									<h6>Personal Details</h6>
									<p>Getting insured is as easy as answering a few short questions about your car, the type of coverage you’re looking to add, and what you want your deductible to be.</p>
								</div>
							</div>
						</div>
						<div class="step2">
							<div class="row">
								<div class="col-2">
									<div class="number">2</div>
								</div>
								<div class="col-10">
									<h6>Submit Application</h6>
									<p>Getting insured is as easy as answering a few short questions about your car, the type of coverage you’re looking to add, and what you want your deductible to be.</p>
								</div>
							</div>
						</div>
						<div class="step3">
							<div class="row">
								<div class="col-2">
									<div class="number">3</div>
								</div>
								<div class="col-10">
									<h6>Recieve Payment</h6>
									<p>Getting insured is as easy as answering a few short questions about your car, the type of coverage you’re looking to add, and what you want your deductible to be.</p>
								</div>
							</div>
						</div>
						<div class="topBottom">
							<a href="{{ route('frontend.loan.start') }}" class="largeButton btn-fill-md">Apply Now</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="testimonials">
		<div class="container">
			<div class="testimonial ">
				<div class="rightContent">
					<h3 class="tx-white">What people say about NodCredit.</h3>
					<p class="tx-white">Recently verified customer stories/feedback</p>
				</div>

				<div class="testimonialContent">
					<div class="nodCard ">
						<p>I get to apply my preferred loan tenor, and choose how my repayment should be spread across active months! which is nice. Very nice...</p>
						<div class="nodCard-img">
							<img src="/assets/images/testimonial/falaolu.jpg" alt="">
						</div>
						<div class="nodCard-info">
							<p>Falasinu Olumide</p>
						</div>
					</div>
					<div class="nodCard">
						<p>...with ability to pause my repayment, and roll over to the coming month, the guys at NodCredit are in tune with real world contingencies when it comes to loan servicing. Very useful feature.</p>
						<div class="nodCard-img">
							<img src="/assets/images/testimonial/timchuks.jpg" alt="">
						</div>
						<div class="nodCard-info">
							<p>Tim Chuks <span>Head of Business Development</span></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>


	@if ($faqItems->count())
	<section class="faq">
		<div class="container">
			<h3>Got Questions about getting loan?</h3>
			<p>Not to worry, we’ve got answers.</p>
			<div class="faqContent">

				<div class="col-12 col-md-9 m-auto">

					<div class="accordion" id="accordionExample">
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
				<div class="row">
					<div class="col-md-8">
						<h3>Get a loan in less than 24 hours</h3>
						<p>We exist to simplify and open up financial access for all in emerging markets - by leveraging technology and widespread mobile adoption.</p>
					</div>
					<div class="col-md-4">
						<div class="fnBtn">
							<a href="{{ route('frontend.loan.start') }}" class="largeButton btn-fill-md">Apply Now</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>


@endsection