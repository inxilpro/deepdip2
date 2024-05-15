<?php

namespace App\Actions;

use App\Data\Floor;
use App\Data\LiveHeight;
use App\Events\StreamerReachedNewFloor;
use App\Support\DeepDipClient;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Lorisleiva\Actions\Concerns\AsAction;
use NotificationChannels\Discord\Discord;

class GetLiveHeights
{
	use AsAction;
	
	public string $commandSignature = 'deepdip:live-heights {threshold=400} {--announce}';
	
	/** @return Collection<int, \App\Data\LiveHeight> */
	public function handle(): Collection
	{
		return app(DeepDipClient::class)->liveHeights()
			->sortByDesc(fn(LiveHeight $height) => $height->height)
			->each(function(LiveHeight $height) {
				if (Floor::at($height->previous_height)->isLowerThan($height->floor)) {
					event(new StreamerReachedNewFloor($height));
				}
			});
	}
	
	public function asCommand(Command $command)
	{
		if ($command->option('announce')) {
			$this->listenForOverThreshold();
		}
		
		$headers = [
			'Rank',
			'User',
			'Floor',
			'Height',
			'Up',
			'Last Update',
		];
		
		$threshold = $command->argument('threshold');
		
		$rows = $this->handle()
			->filter(fn(LiveHeight $height) => $height->height >= $threshold)
			->sortByDesc('height')
			->map(fn(LiveHeight $height) => [
				$height->rank,
				$height->display_name,
				$height->floor->number(),
				number_format($height->height, 2).'m',
				$height->height > ($height->previous_height + 50)
					? '+'.number_format($height->height - $height->previous_height, 2).'m'
					: '',
				$height->timestamp->diffForHumans(),
			])
			->all();
		
		$command->newLine();
		$command->line("Showing streamers higher than <info>{$threshold}m</info>…");
		$command->table($headers, $rows);
		$command->newLine();
		
		return 0;
	}
	
	protected function listenForOverThreshold(): void
	{
		$last_announce_at = Date::createFromTimestamp(Cache::get('last_announce_at', fn() => 0));
		[$min, $avg, $max] = GetThresholds::run();
		
		Event::listen(function(StreamerReachedNewFloor $event) use (&$last_announce_at, $avg, $max) {
			// Don't announce more than every 10 mins, even if leaderboard is topped
			if ($last_announce_at->gt(now()->subMinutes(10))) {
				return;
			}
			
			// Don't send more than once an hour unless someone just topped the leaderboard
			if ($event->height->height < $max && $last_announce_at->gt(now()->subHour())) {
				return;
			}
			
			// Only announce for below-average runs every few hours
			if ($event->height->height < $avg && $last_announce_at->gt(now()->subHours(4))) {
				return;
			}
			
			Cache::forever('last_announce_at', now()->unix());
			$last_announce_at = now();
			
			app(Discord::class)->send(
				channel: '684391298790457466',
				data: [
					'content' => "**{$event->height->display_name}** just reached **{$event->height->floor->name()}**!",
					'embeds' => [
						[
							"id" => now()->unix(),
							"title" => $event->height->display_name,
							"description" => "{$event->height->display_name} is currently at **".number_format($event->height->height, 2).'m**!',
							// "color" => 2326507,
							"footer" => [
								"text" => "Rank {$event->height->rank}",
							],
							"url" => "https://www.twitch.tv/search?term=".urlencode($event->height->display_name),
						],
					],
					'avatar_url' => 'https://cdn.matcherino.com/e2b5bb68-3ea6-4419-b902-6e6da6a1b62c/-/crop/1920x1079/0,0/-/resize/200x200/',
				],
			
			);
		});
	}
}
