<?php

namespace App\Support;

use App\Data\Leaderboard;
use App\Data\LiveHeight;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DeepDipClient
{
	/** @return Collection<int, \App\Data\LiveHeight> */
	public function liveHeights(): Collection
	{
		$response = $this->request()->get('/live_heights/global')->throw();
		
		$previous = Cache::get('live_heights', fn() => []);
		
		$heights = collect($response->json())
			->map(fn($row) => LiveHeight::fromJson($row))
			->each(function(LiveHeight $height) use ($previous) {
				$height->previous_height = $previous[$height->user_id] ?? 0;
			});
		
		Cache::forever('live_heights', $heights->mapWithKeys(fn(LiveHeight $height) => [$height->user_id => $height->height]));
		
		return $heights;
	}
	
	/** @return Collection<int, Leaderboard> */
	public function leaderboard(): Collection
	{
		$response = $this->request()->get('/leaderboard/global')->throw();
		
		return collect($response->json())
			->map(fn($row) => Leaderboard::fromJson($row));
	}
	
	protected function request(): PendingRequest
	{
		return Http::baseUrl('https://dips-plus-plus.xk.io')
			->withUserAgent('personal local-only project')
			->acceptJson();
	}
}
