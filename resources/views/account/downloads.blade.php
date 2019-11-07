@extends('account.layout.main')

@section('content-container-body')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="c-card">
                <h3 class="u-mb-small"><i class="feather icon-download u-pr-small"></i> Download Application</h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="c-card text-center">
                            <a target="_blank" href="https://play.google.com/store/apps/details?id=com.nodcredit.app">
                                <img src="/assets/images/applications/google-play.png" alt="Google Play">
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="c-card text-center">
                            <p class="u-mb-small"><a href="{{ route('account.downloads.app-install.skip') }}" class="c-btn">I don`t have an Android device</a></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection