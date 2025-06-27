<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register observers
        \App\Models\JobListing::observe(\App\Observers\JobListingObserver::class);
        \App\Models\JobApplication::observe(\App\Observers\JobApplicationObserver::class);
    }
}
