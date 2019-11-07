@include('frontend.partials.header')

	<div id="layerslider" class="grey-bkg" style="width: 100%; height: 500px;">
	<div class="ls-slide" style="slidedirection: right; transition2d: 92,93,105; ">
				<span class="ls-s-1 slider-text" style="slidedirection : top; slideoutdirection : bottom; durationin : 1000; durationout : 1000; z-index: 500;">
					<h1>Get Small Loans <br>Starting From <span class="yellow-text"><b>₦{{number_format(get_setting('loan_min'))}}</b></span></h1>
				</span>
		<img src="assets/images/homepage/slide1.jpg" alt="slide1" class="ls-s-1 slider-image" style="slidedirection : right; slideoutdirection : bottom; durationin : 3000; durationout : 3000; ">
	</div>
	<div class="ls-slide" data-ls="transition2d:93;">
				<span class="ls-s-1 slider-text" style="slidedirection : top; slideoutdirection : bottom; durationin : 1000; durationout : 1000; ">
					<h1>Access Personal <br>Loans Of <span class="yellow-text"><b>₦{{number_format(get_setting('loan_max'))}}</b></span></h1>
				</span>
		<img src="assets/images/homepage/slide2.jpg" alt="slide2" class="ls-s-1 slider-image" style=" top:30px; left: 650px; slidedirection : right; slideoutdirection : bottom; durationin : 1500; durationout : 1500; ">
	</div>
</div>
		<div class="home-form">
			<div class="container">
				<div class="row">
					<div class="col-sm-6 col-md-5">
						<!-- the forms switching -->
						<div  id="loan-form"></div>
					</div>
				</div>
			</div>
		</div>
	<div class="nod-small-section">
		<div class="container">
			<div class="row">
				<div class="col-sm-4">
					<div class="row opt-part">
						<div class="col-xs-3">
							<img src="assets/images/homepage/opt11.png" class="img-responsive">
						</div>
						<div class="col-xs-9">
							<h5>Upto ₦{{number_format(get_setting('loan_max'))}} <span>High Range Loan</span></h5>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="row opt-part">
						<div class="col-xs-3">
							<img src="assets/images/homepage/opt12.png" class="img-responsive">
						</div>
						<div class="col-xs-9">
							<h5>Best rate in the market <span>Competitive Interest</span></h5>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="row opt-part">
						<div class="col-xs-3">
							<img src="assets/images/homepage/opt13.png" class="img-responsive">
						</div>
						<div class="col-xs-9">
							<h5>Within minutes <span>Fast & Easy Process</span></h5>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="nod-section grey-bkg center">
		<div class="container">
			<div class="text-headers">
				<h1>Take control of your finances</h1>
				<h5>A personal loan through NodCredit can help you meet your financial goals.</h5>
			</div>
			<div class="row">
				<div class="col-sm-3">
					<div class="nod-card">
						<a href="#">
							<img src="assets/images/homepage/card3.jpg" class="img-responsive">
							<div class="card-content">
								<h5>Travel with ease, knowing we got your back through the journey. Pamper yourself and pay with ease.</h5>
								<h5 class="yellow-text"><b>Travel Support</b></h5>
							</div>
						</a>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="nod-card">
						<a href="#">
							<img src="assets/images/homepage/card4.jpg" class="img-responsive">
							<div class="card-content">
								<h5>Pay for an unexpected home expense or major household purchase in fixed installments instead of waiting and saving for months.</h5>
								<h5 class="yellow-text"><b>Cover Home Expenses</b></h5>
							</div>
						</a>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="nod-card">
						<a href="#">
							<img src="assets/images/homepage/card1.jpg" class="img-responsive">
							<div class="card-content">
								<h5>Don't stretch your monthly keep any further let us get you that fiscal backup you need to make it through all year round.</h5>
								<h5 class="yellow-text"><b>Supplement your salary</b></h5>
							</div>
						</a>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="nod-card">
						<a href="#">
							<img src="assets/images/homepage/card2.jpg" class="img-responsive">
							<div class="card-content">
								<h5>Don't get stuck when it's a matter of life. Every second counts. Let us stand by you in cases of emergency.</h5>
								<h5 class="yellow-text"><b>Get Medical Expenses</b></h5>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="nod-small-section nod-bkg center">
		<div class="container">
			<div class="text-headers">
				<h1 class="white-text">Check what People say about us</h1>
			</div>
			<div id="myTestimonial" class="carousel slide testimonialz" data-ride="carousel">
				<div class="carousel-inner">
					<div class="item active">
						<img src="assets/images/testimonial/falaolu.jpg" class="img-responsive">
						<h4 class="white-text">I get to apply my preferred loan tenor, and choose how my repayment should be spread across active months! which is nice. Very nice...</h4>
						<p class="white-text">Falasinu Olumide</p>
					</div>
					<div class="item">
						<img src="assets/images/testimonial/timchuks.jpg" class="img-responsive">
						<h4 class="white-text">...with ability to pause my repayment, and roll over to the coming month, the guys at NodCredit are in tune with real world contingencies when it comes to loan servicing. Very useful feature.</h4>
						<p class="white-text">Tim Chuks</p>
					</div>
				</div>
				<ol class="carousel-indicators">
					<li data-target="#myTestimonial" data-slide-to="0" class="active"></li>
					<li data-target="#myTestimonial" data-slide-to="1"></li>
				</ol>
				<a class="left carousel-control" href="#myTestimonial" data-slide="prev"><i class="fas fa-chevron-left"></i></a>
				<a class="right carousel-control" href="#myTestimonial" data-slide="next"><i class="fas fa-chevron-right"></i></a>
			</div>
		</div>
	</div>
	<div class="nod-section center">
		<div class="container">
			<div class="text-headers">
				<h1>Super Fast & Easy Application Process</h1>
				<h5>See how you get processed in 4 easy steps.</h5>
			</div>
			<div class="howto-line mobile-delete"></div>
			<div class="row">
				<div class="col-sm-3">
					<div class="howto">
						<div class="howto-line desktop-delete"></div>
						<h1>01</h1>
						<i class="far fa-dot-circle blue-text"></i>
						<h4 class="blue-text"><b>Personal Details</b></h4>
						<p>First we will ask for basic details like your name and email.</p>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="howto">
						<div class="howto-line desktop-delete"></div>
						<h1>02</h1>
						<i class="far fa-dot-circle blue-text"></i>
						<h4 class="blue-text"><b>Enter Employer Name</b></h4>
						<p>Secondly we will ask for basic info about your work.</p>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="howto">
						<div class="howto-line desktop-delete"></div>
						<h1>03</h1>
						<i class="far fa-dot-circle blue-text"></i>
						<h4 class="blue-text"><b>Security credentials</b></h4>
						<p>You will provide BVN, Mobile number, etc.</p>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="howto">
						<div class="howto-line desktop-delete"></div>
						<h1>04</h1>
						<i class="far fa-dot-circle blue-text"></i>
						<h4 class="blue-text"><b>Upload e-Statements</b></h4>
						<p>Our system will do the rest!</p>
					</div>
				</div>
			</div>
		</div>
	</div>
		
		
		
@include('frontend.partials.footer')
