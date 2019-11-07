@extends('account.layout.main')

@section('content-container-body')
	<div class="container">

		@if ($accountUser->canApplyForLoan())
			<div class="row">
				<div class="col-md-12">
					<div class="c-alert c-alert--success u-mb-medium">
						<span class="c-alert__icon">
							<i class="feather icon-check"></i>
						</span>

						<div class="c-alert__content">
							<h4 class="c-alert__title">Apply for a loan</h4>
							<p><a href="{{route('account.profile.apply')}}" class="c-btn c-btn--success">Click here</a> to apply for a loan</p>
						</div>
					</div>
				</div>
			</div>
		@endif


		<div class="row">
			<div class="col-12">

				<nav class="c-tabs">
					<p style="background-color: #f87000; padding: 10px; color: #FFF;">Switch between the tabs to update your profile.</p>
					<div class="c-tabs__list nav nav-tabs" id="myTab" role="tablist">
						<a class="c-tabs__link active" data-toggle="tab" href="#tab-account" role="tab">
							<span class="c-tabs__link-icon"><i class="feather icon-settings"></i></span>
							Account Settings
						</a>

						<a class="c-tabs__link" data-toggle="tab" href="#tab-bank" role="tab">
							<span class="c-tabs__link-icon"><i class="feather icon-sliders"></i></span>
							Bank Account Information
						</a>

						@if($user->role !== 'partner')
							<a class="c-tabs__link" data-toggle="tab" href="#tab-employment" role="tab">
								<span class="c-tabs__link-icon"><i class="feather icon-briefcase"></i></span>
								Employment Information
							</a>

							<a class="c-tabs__link" data-toggle="tab" href="#tab-billing" role="tab">
								<span class="c-tabs__link-icon"><i class="feather icon-credit-card"></i></span>
								Billing Methods (Credit/Debit Card)
							</a>
						@endif
					</div>

					<div class="c-tabs__content tab-content">
						<div class="c-tabs__pane active" id="tab-account" role="tabpanel">

							@if ($errors->any())
								<div class="alert alert-danger">
									<ul>
										@foreach ($errors->all() as $error)
											<li>{{ $error }}</li>
										@endforeach
									</ul>
								</div>
							@endif

							<form action="{{route('account.profile.update')}}" method="post">
								{!! csrf_field() !!}
								<div class="row">
									<div class="col-xl-4">
										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="user-name">Name</label>
											<input
													class="c-input"
													name="name"
													type="text"
													value="{{Auth::user()->name}}"
													{{ auth()->user()->isPartner() ? '' : 'disabled="disabled"' }}
											>
										</div>

										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="user-email">Email Address</label>
											<input
													class="c-input"
													type="text"
													name="email"
													id="user-email"
													value="{{Auth::user()->email}}"
													{{ auth()->user()->isPartner() ? '' : 'disabled="disabled"' }}
											>
										</div>
										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="user-phone">Phone Number</label>
											<input
													class="c-input"
													type="tel"
													name="phone"
													id="user-phone"
													value="{{Auth::user()->phone}}"
													{{ auth()->user()->isPartner() ? '' : 'disabled="disabled"' }}
											>
										</div>

										<div class="c-field u-mb-xsmall">
											<label class="c-switch">
												<input class="c-switch__input" id="user-newsleters" name="newsletter" type="checkbox"
													   value="1" @if(Auth::user()->newsletter) checked="checked" @endif>
												<span class="c-switch__label">Subscribe to Our
															Newsletters</span>
											</label>
										</div>

										<div class="c-field u-mb-xsmall">
											<label class="c-switch">
												<input class="c-switch__input" id="user-tracking" name="track_usage" type="checkbox"
													   value="1" @if(Auth::user()->track_usage) checked="checked" @endif>
												<span class="c-switch__label">Enable Usage Data Tracking</span>
											</label>
										</div>
									</div>

									<div class="col-xl-4">

										<div class="c-field u-mb-xsmall">
											<label class="c-field__label" for="user-plan">What I want</label>
											<div class="c-select">
												<select class="c-select__input" id="user-plan" disabled>
													<option value="1" @if(Auth::user()->role == 'user')
													selected="selected" @endif>I want a loan</option>
													<option value="2" @if(Auth::user()->role == 'partner')
													selected="selected" @endif>I want to Invest</option>
												</select>
											</div>
										</div>

										<div class="c-note u-mb-medium">
													<span class="c-note__icon">
														<i class="feather icon-info"></i>
													</span>
											<p>This option helps us to customize your experience on our platform.</p>
										</div>

										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="user-zip">Date of Birth</label>
											<input
													class="c-input"
													name="dob"
													type="text"
													value="{{Auth::user()->dob}}"
													{{ auth()->user()->isPartner() ? '' : 'disabled="disabled"' }}
											>
										</div>

										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="user-country">Gender</label>
											<div class="c-select">
												<select class="c-select__input" name="gender" id="user-country">
													<option value="male" @if(Auth::user()->gender == 'male')
													selected="selected" @endif>Male</option>
													<option value="female" @if(Auth::user()->gender == 'female')
													selected="selected" @endif>Female</option>
													<option value="others" @if(Auth::user()->gender == 'others')
													selected="selected" @endif>Others</option>
												</select>
											</div>
										</div>
									</div>

									<div class="col-xl-4">

										<div class="c-card u-text-center">
											<div class="c-avatar u-inline-flex">
												<img class="c-avatar__img" src="{{ asset('assets/images/user.svg') }}"
													 alt="{{Auth::user()->name}}">
											</div>

											<h5>{{Auth::user()->name}}</h5>
											<p class="u-pb-small u-mb-small u-border-bottom">
												@if(Auth::user()->isUser())
													Seeking Loan
												@elseif(Auth::user()->isPartner())
													Investor
												@elseif(Auth::user()->isAdmin())
													Administrator
												@elseif(Auth::user()->isSupport())
													Support
												@endif
											</p>
										</div>

									</div>
								</div>

								<span class="c-divider u-mv-medium"></span>

								<div class="row">
									<div class="col-12 col-sm-7 col-xl-2 u-mr-auto u-mb-xsmall">
										<button class="c-btn c-btn--info c-btn--fullwidth">Update
											Profile</button>
									</div>

									<div class="col-12 col-sm-5 col-xl-3 u-text-right">
										<button class="c-btn c-btn--danger c-btn--fullwidth c-btn--outline" type="button"
												onclick="alert('Please contact the admin');">Delete My
											Account</button>
									</div>
								</div>
							</form>
						</div>

						<div class="c-tabs__pane" id="tab-bank" role="tabpanel">
							<form action="" id="account-form">

								{!! csrf_field() !!}
								<div class="row">
									<div class="col-xl-4">
										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="account_name">Account Name</label>
											<input class="c-input"
												   type="text"
												   value="{{Auth::user()->name}}"
												   disabled="disabled"
											>
											<small>Note: We use your "Name" as "Account Name"</small>
										</div>

										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="account_number">Account
												Number</label>
											<input class="c-input"
												   type="text"
												   name="account_number"
												   id="account_number"
												   value="{{Auth::user()->account_number}}"
											>
										</div>
									</div>

									<div class="col-xl-4">
										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="user-bank">Select Your
												Bank</label>
											<div class="c-select">
												<select class="c-select__input" id="user-bank" name="bank">
													@foreach($banks as $bank)
														<option value="{{$bank->id}}" @if(Auth::user()->bank &&
																Auth::user()->bank->id == $bank->id) selected="selected"
																@endif>{{$bank->name}}</option>
													@endforeach
												</select>
											</div>
										</div>

										<div class="c-field u-mb-medium">
											<label class="c-field__label" for="user-bvn">BVN</label>
											<input
													class="c-input"
													type="text"
													name="bvn"
													id="user-bvn"
													value="{{Auth::user()->bvn}}"
													{{ auth()->user()->isPartner() ? '' : 'disabled="disabled"' }}
											>
										</div>
									</div>

									<div class="col-xl-4">

										<div class="c-card u-text-center">
											<div class="c-avatar u-inline-flex">
												<img class="c-avatar__img" src="{{ asset('assets/images/user.svg') }}"
													 alt="{{Auth::user()->name}}">
											</div>

											<h5>{{Auth::user()->name}}</h5>
											<p class="u-pb-small u-mb-small u-border-bottom">
												@if(Auth::user()->isUser())
													Seeking Loan
												@elseif(Auth::user()->isPartner())
													Investor
												@elseif(Auth::user()->isAdmin())
													Administrator
												@elseif(Auth::user()->isSupport())
													Support
												@endif
											</p>
										</div>

									</div>
								</div>

								<span class="c-divider u-mv-medium"></span>

								<div class="row">
									<div class="col-12 col-sm-7 col-xl-2 u-mr-auto u-mb-xsmall">
										<button class="c-btn c-btn--info c-btn--fullwidth" id="update-account-btn">Update
											Account</button>
									</div>

									<div class="col-12 col-sm-5 col-xl-3 u-text-right">
										<button class="c-btn c-btn--danger c-btn--fullwidth c-btn--outline" data-toggle="modal"
												data-target="#modal-delete">Delete My
											Account</button>
									</div>
								</div>

							</form>
						</div>

						@if($user->role !== 'partner')
							<div class="c-tabs__pane" id="tab-employment" role="tabpanel">

								<div id="work-hisotry"></div>
							</div>

							<div class="c-tabs__pane" id="tab-billing" role="tabpanel">
								<div class="row">
									<div class="col-lg-4 u-mb-xsmall">
										<button class="c-btn c-btn--fullwidth c-btn--large" id="link-card-btn"
												style="margin-bottom: 30px;">ADD CARD</button>
									</div>
									<div class="col-lg-8"></div>

									@if(Auth::user()->cards()->count())

										@foreach(Auth::user()->cards as $card)
											<div class="col-lg-4 col-md-6 u-mb-xsmall">
												<div class="c-alert c-alert--success u-mb-medium">
												<span class="c-alert__icon">
													<i class="feather icon-check"></i>
												</span>
													<div class="c-alert__content">
														<h4 class="c-alert__title">Card Linked</h4>
														<p>
															<strong>Card Type: </strong>{{$card->card_type}}
														</p>
														<p>
															<strong>Card Number: </strong>{{$card->card_number}}
														</p>

														<p>
															<strong>Expire Date: </strong>{{$card->exp_month}} /
															{{$card->exp_year}}
														</p>

														<p style="margin-top: 30px;">
															<a href="?removeCard=yes&card={{$card->id}}" class="c-btn c-btn--danger c-btn--fullwidth"
															   onclick="return confirm('Are you sure you want to remove this card? Click Ok to confirm.')">Remove
																card</a>
														</p>
													</div>
												</div>
											</div>
										@endforeach
									@endif
								</div>

							</div>
						@endif

					</div>
				</nav>
			</div>
		</div>
	</div>
@endsection

@section('scripts-footer')
	<script src="{{asset('js/work-history.js')}}"></script>
@endsection

