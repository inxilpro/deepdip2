<?php

namespace App\Actions;

use App\Data\Floor;
use App\Data\Leaderboard;
use App\Support\DeepDipClient;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class GetThresholds
{
	use AsAction;
	
	public string $commandSignature = 'deepdip:thresholds';
	
	public function handle(?Closure $onRefresh = null): array
	{
		$leaderboard = Cache::remember('leaderboard-thresholds', now()->addHour(), function() use ($onRefresh) {
			if ($onRefresh) {
				$onRefresh();
			}
			
			return app(DeepDipClient::class)
				->leaderboard()
				->filter(fn(Leaderboard $row) => $row->rank <= 10)
				->map(fn(Leaderboard $row) => $row->height)
				->all();
		});
		
		return [
			collect($leaderboard)->min(),
			collect($leaderboard)->avg(),
			collect($leaderboard)->max(),
		];
	}
	
	public function asCommand(Command $command)
	{
		$command->newLine();
		
		[$min, $avg, $max] = $this->handle(fn() => $command->line("Fetching new leaderboard data…\n"));
		
		$command->line(sprintf('Min:     <info>%s</info> (%d meters)', Floor::at($min)->name(), $min));
		$command->line(sprintf('Average: <info>%s</info> (%d meters)', Floor::at($avg)->name(), $avg));
		$command->line(sprintf('Max:     <info>%s</info> (%d meters)', Floor::at($max)->name(), $max));
		
		$command->newLine();
		
		return 0;
	}
}
