<?php

namespace App\Data;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

class Leaderboard
{
	public Floor $floor;
	
	public static function fromJson(array|object $data): static
	{
		return new static(
			rank: data_get($data, 'rank', 0),
			wsid: data_get($data, 'wsid', ''),
			height: data_get($data, 'height', 0),
			timestamp: Date::createFromTimestamp(data_get($data, 'ts', now()->unix())),
			name: data_get($data, 'name', 'N/A'),
			update_count: data_get($data, 'update_count', 0),
			color: data_get($data, 'color', []),
		);
	}
	
	public function __construct(
		public int $rank,
		public string $wsid,
		public float $height,
		public CarbonInterface $timestamp,
		public string $name,
		public int $update_count,
		public array $color,
	) {
		$this->floor = Floor::at($this->height);
	}
	
	public function toArray(): array
	{
		return [
			'rank' => $this->rank,
			'wsid' => $this->wsid,
			'height' => $this->height,
			'ts' => $this->timestamp->unix(),
			'name' => $this->name,
			'update_count' => $this->update_count,
			'color' => $this->color,
		];
	}
}
