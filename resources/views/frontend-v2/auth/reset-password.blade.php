@extends('frontend-v2.layout.main')

@section('body_class') page-start @endsection

@section('footer_class') g3 @endsection

@section('head')
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.1.1/css/mdb.min.css">
@endsection

@section('body')

	<header>
		<div class="container">
			<div class="navigation">
				<div class="brand">
					<a href="/v2"><img src="/frontend/images/logo.svg" alt="Nod Credit logo"></a>
				</div>
			</div>
		</div>
	</header>

	<section class="startSignup">
		<div class="container">
			<div class="col-12 col-md-11 m-auto">
				<auth-reset-password :token="'{{ $token }}'" />
			</div>
		</div>
	</section>
@endsection