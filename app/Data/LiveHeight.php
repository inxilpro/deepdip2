<?php

namespace App\Data;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

class LiveHeight
{
	public static function fromJson(array|object $data): static 
	{
		return new static(
			display_name: data_get($data, 'display_name'),
			user_id: data_get($data, 'user_id'),
			height: data_get($data, 'height'),
			timestamp: Date::createFromTimestamp(data_get($data, 'ts')),
			rank: data_get($data, 'rank'),
			previous_height: data_get($data, 'previous_height', 0),
		);
	}
	
	public function __construct(
		public string $display_name,
		public string $user_id,
		public float $height,
		public CarbonInterface $timestamp,
		public int $rank,
		public float $previous_height = 0,
	)
	{
	}
	
	public function toArray(): array
	{
		return [
			'display_name' => $this->display_name,
			'user_id' => $this->user_id,
			'height' => $this->height,
			'previous_height' => $this->previous_height,
			'ts' => $this->timestamp->unix(),
			'rank' => $this->rank,
		];
	}
}
