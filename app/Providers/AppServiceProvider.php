<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Facades\Actions;
use TwitchApi\HelixGuzzleClient;
use TwitchApi\TwitchApi;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TwitchApi::class, function() {
	        $twitch_client_id = config('services.twitch.client_id');
	        $twitch_client_secret = config('services.twitch.secret');
	        
	        return new TwitchApi(
				helixGuzzleClient: new HelixGuzzleClient($twitch_client_id), 
				clientId: $twitch_client_id, 
				clientSecret: $twitch_client_secret
	        );
        });
    }
	
    public function boot(): void
    {
        Actions::registerCommands();
    }
}
