<?php

namespace Tests\Unit;

use App\Support\Frontend\AdminGrowthDelta;
use Tests\TestCase;

class AdminGrowthDeltaTest extends TestCase
{
    public function test_percent_mode_when_previous_positive(): void
    {
        $delta = AdminGrowthDelta::from(12, 10);

        $this->assertTrue($delta['available']);
        $this->assertSame('percent', $delta['mode']);
        $this->assertSame(20, $delta['percent']);
        $this->assertSame('up', $delta['direction']);
    }

    public function test_does_not_invent_100_percent_when_previous_is_zero(): void
    {
        $delta = AdminGrowthDelta::from(5, 0);

        $this->assertTrue($delta['available']);
        $this->assertSame('new_baseline', $delta['mode']);
        $this->assertNull($delta['percent']);
        $this->assertSame(5, $delta['current']);
    }

    public function test_flat_zero_when_both_periods_zero(): void
    {
        $delta = AdminGrowthDelta::from(0, 0);

        $this->assertSame('flat_zero', $delta['mode']);
        $this->assertSame(0, $delta['percent']);
        $this->assertSame('flat', $delta['direction']);
    }

    public function test_negative_percent_when_current_lower(): void
    {
        $delta = AdminGrowthDelta::from(5, 10);

        $this->assertSame('percent', $delta['mode']);
        $this->assertSame(-50, $delta['percent']);
        $this->assertSame('down', $delta['direction']);
    }

    public function test_unavailable_when_inputs_missing(): void
    {
        $delta = AdminGrowthDelta::from(null, 0);

        $this->assertFalse($delta['available']);
        $this->assertSame('unavailable', $delta['mode']);
        $this->assertNull($delta['percent']);
    }
}
