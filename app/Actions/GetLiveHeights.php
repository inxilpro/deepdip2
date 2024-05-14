<?php

namespace App\Actions;

use App\Data\LiveHeight;
use App\Events\StreamerPassedThresholdEvent;
use App\Support\DeepDipClient;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Lorisleiva\Actions\Concerns\AsAction;
use NotificationChannels\Discord\Discord;

class GetLiveHeights
{
	use AsAction;
	
	public string $commandSignature = 'deepdip:live-heights {threshold=400} {--announce}';
	
	public function __construct()
	{
		$threshold = config('app.threshold', 1100);
		$this->commandSignature = "deepdip:live-heights {threshold={$threshold}} {--announce}";
	}
	
	/** @return Collection<int, \App\Data\LiveHeight> */
	public function handle(): Collection
	{
		return app(DeepDipClient::class)->liveHeights();
	}
	
	public function asCommand(Command $command)
	{
		if ($command->option('announce')) {
			$this->listenForOverThreshold();
		}
		
		$headers = [
			'Rank',
			'User',
			'Previous',
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
				number_format($height->previous_height, 2).'m',
				number_format($height->height, 2).'m',
				$height->height > ($height->previous_height + 50)
					? '+'.number_format($height->height - $height->previous_height, 2).'m'
					: '',
				$height->timestamp->diffForHumans(),
			])
			->all();
		
		$command->newLine();
		$command->line("Showing streamers higher than <info>{$threshold}</info>");
		$command->table($headers, $rows);
		$command->newLine();
		
		return 0;
	}
	
	protected function listenForOverThreshold(): void
	{
		Event::listen(function(StreamerPassedThresholdEvent $event) {
			app(Discord::class)->send(
				channel: '684391298790457466',
				data: [
					'content' => "**{$event->height->display_name}** is over {$event->threshold}m and climbing!",
					'embeds' => [
						[
							"id" => 652627557,
							"title" => $event->height->display_name,
							"description" => "Currently at **".number_format($event->height->height, 2).'m**!',
							"color" => 2326507,
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
