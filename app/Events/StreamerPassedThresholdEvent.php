<?php

namespace App\Events;

use App\Data\LiveHeight;
use Illuminate\Foundation\Events\Dispatchable;

class StreamerPassedThresholdEvent
{
	use Dispatchable;
	
	public function __construct(
		public LiveHeight $height,
		public int $threshold
	) {
	}
}
