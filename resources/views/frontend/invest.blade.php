@include('frontend.partials.header')
{{-- <div class="nod-bready grey-bkg center">
	<div class="container">
		<h1>Terms & Conditions</h1>
		<h4>Effective Date: August 05, 2018</h4>
	</div>
</div> --}}
<div class="nod-section grey-bkg">
  <div class="container">
    <div class="row">
      <div class="col-sm-5 invest">
        <h2>Make Secure Investment on NodCredit</h2>
        <p>Your money can work for you while you sit back and chill. We offer market leading interest rates, on very
          flexible maturity tenors.</p>
        <p>Earn 20 - 30% p.a on your money.</p>
        <a href="/register?want=2" class="btn blue-bkg btn-lg btn-invest">Start Investing</a>
      </div>
      <div class="col-sm-7">
        <img class="img-responsive" src="assets/images/undraw_wallet_aym5.svg">
      </div>
    </div>

    <div class="row pt-7">
      <div class="col-sm-12 pb-3 text-center">
        <h2>Nodcredit Invest - How it works.</h2>
      </div>
      <div class="col-sm-4 text-center">
        <img src="assets/images/login.svg" width="70px" class="img-responsive">
        <p>Sign up on NodCredit, using your BVN and other matching details. Confirm your registration.</p>
      </div>
      <div class="col-sm-4 text-center">
        <img src="assets/images/home.svg" width="70px" class="img-responsive">
        <p>Access your account and click on the invest menu link, enter your preferred interest rates & tenor.</p>
      </div>
      <div class="col-sm-4 text-center">
        <img src="assets/images/savings.svg" width="70px" class="img-responsive">
        <p>Make investment deposit via card or bank deposit. Sit back and watch your money grow.</p>
      </div>
    </div>

    <div class="row py-7">
      <div class="col-lg-12 pb-3 text-center">
        <h2>Frequently Asked Questions on Investment</h2>
      </div>
      <div class="col-md-5 faq-title">
        <h1>How can we help you?</h1>
        <p>We are always here to help whenever you might have some questions! You can check our frequently asked questions and also contact us via email invest@nodcredit.com or call us on +234 818 8150 981</p>
        <div class="py-5">
        <a href="{{route('ui.auth.register')}}?want=2" class="btn blue-bkg btn-lg btn-invest">Create An Account</a>
      </div>
      </div>
      <div class="col-lg-7">
        <div class="panel-group" id="accordion">
          <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
                <h4 class="panel-title">I want to Invest on Nodcredit platform, what does it mean, and do I get my funds on demand?</h4>
              </a>
            </div>
            <div id="collapse1" class="panel-collapse collapse in">
              <div class="panel-body">You can earn good returns on your investments with us. Nodcredit makes your money
                work for you while you earn good returns. Pre-liquidation, either fully or part liquidation is on
                demand, instant and completely automated. See our terms of service for charges and other conditions.
              </div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">
                <h4 class="panel-title">How long does it take to get my money after liquidation or at maturity?</h4>
              </a>
            </div>
            <div id="collapse2" class="panel-collapse collapse">
              <div class="panel-body">We credit our investors instantly when their investments mature or if the investment is liquidated before maturity. However, for large investments it may take up to 3 – 5 working days for funds to reach the investors bank account.</div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
                <h4 class="panel-title">What is the minimum tenor I can invest my funds?</h4>
              </a>
            </div>
            <div id="collapse3" class="panel-collapse collapse">
              <div class="panel-body">You can place funds with Nodcredit for 92 days, 184 days and 365 days. The minimum tenor is 92 days and the maximum tenor is 365 days. </div>
          </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse4">
                <h4 class="panel-title">How much Interest do I earn on my Investment?</h4>
              </a>
            </div>
            <div id="collapse4" class="panel-collapse collapse">
              <div class="panel-body">Our interest rates vary from time to time, you can find applicable rates for chosen tenor when you register and sign in to a Nodcredit account.</div>
          </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse5">
                <h4 class="panel-title">Are interests payable monthly or at maturity?</h4>
              </a>
            </div>
            <div id="collapse5" class="panel-collapse collapse">
              <div class="panel-body">Interests are calculated on a per annum basis, and payable at maturity or when the investment is liquidated.</div>
          </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse6">
                <h4 class="panel-title">Are there penalties for early liquidation?</h4>
              </a>
            </div>
            <div id="collapse6" class="panel-collapse collapse">
              <div class="panel-body">We charge 40% on the interest earned if you liquidate your investment before maturity. Your principal sum is not affected in any case.</div>
          </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse7">
                <h4 class="panel-title">I like what you guys are doing at Nodcredit, I want to invest a high amount, can I negotiate?</h4>
              </a>
            </div>
            <div id="collapse7" class="panel-collapse collapse">
              <div class="panel-body">Yes, you can negotiate investments above certain amount; send an email to us at invest@nodcredit.com</div>
          </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse8">
                <h4 class="panel-title">How am I sure I will get my money back, is this a fixed term deposit?</h4>
              </a>
            </div>
            <div id="collapse8" class="panel-collapse collapse">
              <div class="panel-body">We disburse thousands of loans to Nigerians weekly and we are growing daily. Investments on Nodcredit helps us reach more people and drive consumer finance in Nigeria. All Investments are on Nodcredit as an entity. Investment on Nodcredit only shares similar features as a fixed term deposit.</div>
        </div>
      </div>
      <div class="panel panel-default">
	<div class="panel-heading">
		<a data-toggle="collapse" data-parent="#accordion" href="#collapse9">
			<h4 class="panel-title">I want to be part of Nodcredit, Can I convert my Investment to Equity?</h4>
		</a>
	</div>
	<div id="collapse9" class="panel-collapse collapse">
		<div class="panel-body">Yes, you can. Our goal is to drive consumer finance and boost economic activities. If you want to do the same, send us an email at invest@nodcredit.com</div>
	</div>
</div>
</div>


    </div>
  </div>
</div>

@include('frontend.partials.footer')