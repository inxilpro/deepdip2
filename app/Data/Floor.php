<?php

namespace App\Data;

enum Floor: int
{
	protected const MAP = [
		0 => self::Floor0,
		104 => self::Floor1,
		208 => self::Floor2,
		312 => self::Floor3,
		416 => self::Floor4,
		520 => self::Floor5,
		624 => self::Floor6,
		728 => self::Floor7,
		832 => self::Floor8,
		936 => self::Floor9,
		1040 => self::Floor10,
		1144 => self::Floor11,
		1264 => self::Floor12,
		1376 => self::Floor13,
		1480 => self::Floor14,
		1584 => self::Floor15,
		1688 => self::Floor16,
		1910 => self::Floor17,
	];
	
	case Floor0 = 0;
	case Floor1 = 104;
	case Floor2 = 208;
	case Floor3 = 312;
	case Floor4 = 416;
	case Floor5 = 520;
	case Floor6 = 624;
	case Floor7 = 728;
	case Floor8 = 832;
	case Floor9 = 936;
	case Floor10 = 1040;
	case Floor11 = 1144;
	case Floor12 = 1264;
	case Floor13 = 1376;
	case Floor14 = 1480;
	case Floor15 = 1584;
	case Floor16 = 1688;
	case Floor17 = 1910;
	
	public static function at(int|LiveHeight $height): self
	{
		if ($height instanceof LiveHeight) {
			$height = $height->height;
		}
		
		$floor = self::Floor0;
		
		foreach (self::MAP as $meters => $next) {
			if ($height < $meters) {
				return $floor;
			}
			
			$floor = $next;
		}
		
		return $floor;
	}
	
	public function isHigherThan(self $other): bool
	{
		return $this->number() > $other->number();
	}
	
	public function isLowerThan(self $other): bool
	{
		return $this->number() < $other->number();
	}
	
	public function name(): string
	{
		return 'Floor '.$this->number();
	}
	
	public function number(): int
	{
		return str($this->name)->after('Floor')->toInteger();
	}
}
