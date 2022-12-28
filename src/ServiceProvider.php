<?php

namespace DatabaseDiff;

use DatabaseDiff\Console\DatabaseDiff;
use DatabaseDiff\Console\DatabaseRun;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
   
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {


    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            DatabaseDiff::class,
            DatabaseRun::class
        ]);
    }
}
