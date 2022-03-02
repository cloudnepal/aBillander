<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Laravel uses the utf8mb4 character set by default. If you are running a version of MySQL older than the 5.7.7 release or MariaDB older than the 10.2.2 release, you may need to manually configure the default string length generated by migrations in order for MySQL to create indexes for them. 
        // See: https://stackoverflow.com/questions/23786359/laravel-migration-unique-key-is-too-long-even-if-specified#answer-39750202
//        Schema::defaultStringLength(191);

        // See: https://stackoverflow.com/questions/35827062/how-to-force-laravel-project-to-use-https-for-all-routes/51819095
        // https://www.kodementor.com/force-http-to-https-redirect-in-laravel/
        // https://hdtuto.com/article/how-to-force-redirect-http-to-https-in-laravel-55-

        // \Illuminate\Support\Facades\URL::forceScheme('https');
        // if (\App::environment() === 'production') {
        if(env('ENFORCE_SSL', false)) {
                 \Illuminate\Support\Facades\URL::forceScheme('https');
            }
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

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
