<?php

namespace Lightit\LinkedinShare;

use Illuminate\Support\ServiceProvider;

class LinkedinShareServiceProvider extends ServiceProvider
{
    protected $redirect_uri;
    protected $client_id;
    protected $client_secret;

    public function boot()
    {
        $this->publishes([__DIR__.'/config/linkedinshare.php' => config_path('linkedinshare.php')], 'linkedin-share');

        $this->redirect_uri = config('linkedinshare.redirect_uri');
        $this->client_id = config('linkedinshare.client_id');
        $this->client_secret = config('linkedinshare.client_secret');
    }

    public function register()
    {
        $this->app->singleton(LinkedinShare::class, function () {
            return new LinkedinShare($this->redirect_uri, $this->client_id, $this->client_secret);
        });
    }
}
