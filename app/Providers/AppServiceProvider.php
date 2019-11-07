<?php

namespace App\Providers;

use App\LoanPayment;
use App\NodCredit\Account\User;
use App\NodCredit\Docparser\NodcreditDocparser;
use App\NodCredit\Settings;
use App\Observers\LoanPaymentObserver;
use App\Paystack\PaystackApi;
use App\Services\Sling\SlingApi;
use App\Setting;
use Docparser\Docparser;
use Geocoder\StatefulGeocoder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/', (string)$value);
        }, 'Password must at least contain uppercase/lowercase letters, one number and one special character');

        LoanPayment::observe(LoanPaymentObserver::class);

        // NodCredit Settings
        $this->app->singleton(Settings::class, function() {
            $models = Setting::all();

            return new Settings($models);
        });

        // NodCredit User
        $this->app->singleton(User::class, function() {

            if (auth()->user()) {
                return new User(auth()->user());
            }

            return null;
        });

        // Docparser
        $this->app->bind(Docparser::class, function () {
            return new NodcreditDocparser(config('docparser.token'));
        });

        // Paystack
        $this->app->bind(PaystackApi::class, function () {
            return new PaystackApi(config('services.paystack'));
        });

        // Geocoder
        $this->app->bind(StatefulGeocoder::class, function() {
            $httpClient = new \Http\Adapter\Guzzle6\Client();

            $provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, null, config('services.google_cloud_platform.api_key'));

            $geocoder = new \Geocoder\StatefulGeocoder($provider, 'en');

            return $geocoder;
        });

        // Sling
        $this->app->bind(SlingApi::class, function () {
            return new SlingApi(config('services.sling'));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
