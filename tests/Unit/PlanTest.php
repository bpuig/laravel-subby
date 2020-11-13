<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Tests\TestCase;

class PlanTest extends TestCase
{
    /**
     * Test Plan creation with already existing tag in database
     */
    public function testUnableToCreatePlanWithExistingTag()
    {
        $this->expectException('Illuminate\Database\QueryException');
        $this->expectExceptionMessage('UNIQUE constraint failed: plans.tag');
        Plan::create([
            'tag' => 'basic',
            'name' => 'New Basic Plan',
            'description' => 'This plan cannot exist.',
            'currency' => 'EUR'
        ]);
    }
}
