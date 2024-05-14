<?php

namespace App\Actions;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use TwitchApi\HelixGuzzleClient;
use TwitchApi\TwitchApi;

class SetupTwitch
{
	use AsAction;
	
	public string $commandSignature = 'setup:twitch';
	
	public function handle(TwitchApi $api)
	{
		$bearer = $this->bearerToken($api);
		
		$result = $api->getUsersApi()->getUserById($bearer, 254710647);
		
		// $result = $api->getUsersApi()->getUserByUsername($bearer, 'inxilpro');
		dump($result->getBody()->getContents());
		// $api->getChannelsApi()->getChannelInfo($bearer, 1);
	}
	
	public function asCommand(Command $command)
	{
		$this->handle(app(TwitchApi::class));
		
		$command->info('Done');
	}
	
	protected function bearerToken(TwitchApi $api): string
	{
		if (Cache::has('twitch_bearer_token')) {
			return Cache::get('twitch_bearer_token');
		}
		
		$oauth = $api->getOauthApi();
		$token = $oauth->getAppAccessToken();
		$data = json_decode($token->getBody()->getContents());
		
		Cache::put('twitch_bearer_token', $data->access_token, $data->expires_in - 60);
		
		return $data->access_token;
	}
}
