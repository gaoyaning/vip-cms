<?php
namespace App\Providers;

use App\Libs\Logger;
use Illuminate\Support\ServiceProvider;

class NewLogServiceProvider extends ServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('log', function () {
            return new Logger($this->app);
        });
    }
}
