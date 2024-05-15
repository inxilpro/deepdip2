<?php

namespace Tests\Unit;

use App\Data\Floor;
use PHPUnit\Framework\TestCase;

class FloorTest extends TestCase
{
    public function test_floor_at(): void
    {
        $this->assertEquals(Floor::Floor0, Floor::at(0));
        $this->assertEquals(Floor::Floor0, Floor::at(103));
        $this->assertEquals(Floor::Floor1, Floor::at(104));
        $this->assertEquals(Floor::Floor1, Floor::at(207));
        $this->assertEquals(Floor::Floor2, Floor::at(208));
        $this->assertEquals(Floor::Floor10, Floor::at(1050));
        $this->assertEquals(Floor::Floor13, Floor::at(1380));
		
    }
}
