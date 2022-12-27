<?php

namespace DatabaseDiff;

use DatabaseDiff\Console\DatabaseDiff;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
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
            DatabaseDiff::class
        ]);
    }
}
