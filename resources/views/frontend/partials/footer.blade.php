		
		<div class="footer-v8" id="footer-v8">
			<footer class="footer dark-bkg">
				<div class="container">
					<div class="row">
							<div class="col-sm-3 col-xs-6">
								<h5>Company</h5>
								<li><a href="/invest">Invest</a></li>
								<li><a href="/get-loan">Get Loan</a></li>
								<li><a href="/build-trust">Build trust</a></li>
							</div>
							<div class="col-sm-3 col-xs-6 the-end">
								<h5>Help & Support</h5>
								<li><a href="/faq">FAQ’s</a></li>
								<li><a href="#">Contact Us</a></li>
							</div>
							<div class="col-sm-3 col-xs-6 the-end">
								<h5>Legal</h5>
								<li><a href="/term-conditions">Terms and Conditions</a></li>
								<li><a href="/privacy-policy">Privacy Policy</a></li>
								{{-- <li><a href="#">Cookies Policy</a></li> --}}
								<li><a href="/loan-eligibility">Loan Eligibility</a></li>
							</div>
						<div class="col-sm-3 col-xs-6">
							<h5>Follow Us</h5>
							{{-- <div class="input-group">
								<input class="form-control" type="email" placeholder="your email address">
								<div class="input-group-btn">
									<button type="button" class="btn-u input-btn">Sign Up</button>
								</div>
							</div> --}}
							<ul class="social-icon-list margin-bottom-20">
								<li><a href="https://www.twitter.com/nodcredit/"><i class="rounded-x fa fa-twitter"></i></a></li>
								<li><a href="https://www.facebook.com/nodcredit/"><i class="rounded-x fa fa-facebook"></i></a></li>
								<li><a href="https://www.linkedin.com/nodcredit"><i class="rounded-x fa fa-linkedin"></i></a></li>
								{{-- <li><a href="#"><i class="rounded-x fa fa-google-plus"></i></a></li> --}}

							</ul>
						</div>
					</div>
				</div>
			</footer>
		</div>
		
	</div>

	<script type="text/javascript" src="assets/plugins/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="assets/plugins/jquery/jquery-migrate.min.js"></script>
	<script type="text/javascript" src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/plugins/back-to-top.js"></script>
	<script type="text/javascript" src="assets/plugins/smoothScroll.js"></script>
	<script type="text/javascript" src="assets/plugins/owl-carousel/owl-carousel/owl.carousel.js"></script>
	<script type="text/javascript" src="assets/plugins/layer-slider/layerslider/js/greensock.js"></script>
	<script type="text/javascript" src="assets/plugins/layer-slider/layerslider/js/layerslider.transitions.js"></script>
	<script type="text/javascript" src="assets/plugins/layer-slider/layerslider/js/layerslider.kreaturamedia.jquery.js"></script>
	<script type="text/javascript" src="assets/js/custom.js"></script>
	<script type="text/javascript" src="assets/js/app.js"></script>
	<script type="text/javascript" src="assets/js/plugins/layer-slider.js"></script>
	<script type="text/javascript" src="assets/js/plugins/style-switcher.js"></script>
	<script type="text/javascript" src="assets/js/plugins/owl-carousel.js"></script>
	<script type="text/javascript" src="assets/js/plugins/owl-recent-works.js"></script>
	<script src="assets/plugins/sky-forms-pro/skyforms/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/form-sliders.js"></script>
	
	<script type="text/javascript">
		jQuery(document).ready(function() {
			App.init();
			LayerSlider.initLayerSlider();
			StyleSwitcher.initStyleSwitcher();
			OwlCarousel.initOwlCarousel();
			OwlRecentWorks.initOwlRecentWorksV2();
		});
		
		jQuery(document).ready(function() {
			App.init();
			StyleSwitcher.initStyleSwitcher();
			FormSliders.initFormSliders();
		});

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
	</script>
	
	
<!--[if lt IE 9]>
	<script src="assets/plugins/respond.js"></script>
	<script src="assets/plugins/html5shiv.js"></script>
	<script src="assets/plugins/placeholder-IE-fixes.js"></script>
	<![endif]-->
		<script src="{{asset('js/loan-form.js')}}"></script>
</body>
</html>
