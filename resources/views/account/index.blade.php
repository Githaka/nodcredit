@extends('account.layout.main')

@section('content-header')
	<div class="c-navbar c-navbar--transparent" style="margin-bottom: 30px;">

		<a href="{{route('account.profile.apply')}}" class="c-btn c-btn--success u-ml-small u-mv-xsmall">
			<i class="fas fa-wallet u-mr-xsmall"></i>Apply for Loan
		</a>

		<a href="{{route('account.profile.invest')}}" class="c-btn c-btn--info u-ml-auto u-mv-xsmall">
			<i class="fas fa-money-bill-wave u-mr-xsmall"></i>Invest
		</a>

		<a href="mailto:support@nodcredit.com" class="c-btn c-btn--secondary u-ml-small u-mv-xsmall">
			<i class="feather icon-mail u-mr-xsmall"></i>Contact
		</a>
	</div>
@endsection

@section('content-container-body')

	<div class="row justify-content-center">
		<div class="col-lg-10">
			<div class="row">
				<div class="col-md-6">
					<div class="c-state-card c-state-card--info">
						<h3 class="c-text--subtitle u-text-white">Total Received</h3>
						<h2 class="u-text-white">&#8358;{{number_format($totalRecieved)}}</h2>
					</div>
				</div>

				<div class="col-md-6">
					<div class="c-state-card c-state-card--fancy">
						<div class="row">
							<div class="col-md-6">
								<h3 class="c-text--subtitle u-text-white">Completed</h3>
								<h2 class="u-text-white">{{ $completedApplicationsCount }}</h2>
							</div>
							<div class="col-md-6">
								<h3 class="c-text--subtitle u-text-white">Rejected</h3>
								<h2 class="u-text-white">{{ $rejectedApplicationsCount }}</h2>
							</div>
						</div>
					</div>
				</div>

				@if(Auth::user()->role == 'admin')
					<div class="col-md-6">
						<div class="c-state-card c-state-card--fancy">
							<h3 class="c-text--subtitle u-text-white">Customers</h3>
							<h2 class="u-text-white">{{number_format($customers)}}</h>
						</div>
					</div>


					<div class="col-md-6">
						<div class="c-state-card c-state-card--info">
							{{-- <span class="c-icon c-icon--warning u-mb-small">
                                <i class="feather icon-zap"></i>
                            </span> --}}

							<h3 class="c-text--subtitle u-text-white">Investors</h3>
							<h2 class="u-text-white">{{number_format($investors)}}</h2>
						</div>
					</div>

				@endif
			</div>

		</div>

		<div class="col-lg-10">
			<div class="row">
				<div class="col-md-8">
					<div class="c-card">
						<h3 class="u-mb-small"><i class="feather icon-user-plus u-pr-small"></i> NodScore - what helps</h3>
						<div class="row">
							<div class="col-md-3">
								<h5>Your Score is</h5>
								<h1 class="nc-score">{{ number_format(auth()->user()->getScores()) }}</h1>
							</div>
							<div class="col-md-9 border-left-1">
								<ul class="ncs-list">
									<li><i class="feather icon-chevron-right"></i> Making loan repayment on
										time</li>
									<li><i class="feather icon-chevron-right"></i> Completing your profile
										details, it helps your score when we know as much as possible about
										you</li>
									<li><i class="feather icon-chevron-right"></i> Using the pause option
										doesn't affect your score negatively</li>
								</ul>
							</div>
						</div>
					</div>

				</div>

				<div class="col-md-4">
					<div class="c-card">
						<h3 class="u-mb-small"><i class="feather icon-award u-pr-small"></i> Rewards</h3>
						<p class="u-pb-small">Some of the rewards for keeping good NodScores..</p>
						<ul class="ncr-list">
							<li>Access higher loans
								<br>
								<small><strong>you can access upto 50,000 now</strong></small></li>
							<li>Exclusive Interest on loans
								<br><small><strong>you are on 15% interest now</strong></small></li>
							<li>Privileged Shopper on our store
								<br><small><strong>coming soon</strong></small></li>
							<li>and much more</li>
						</ul>
					</div>
				</div>


			</div>
		</div>

	</div>

@endsection