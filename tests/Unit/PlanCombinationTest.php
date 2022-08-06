<?php


namespace Bpuig\Subby\Tests\Unit;


use Bpuig\Subby\Tests\TestCase;

class PlanCombinationTest extends TestCase
{
    /**
     * Test Plan Combination creation with already existing tag in database
     */
    public function testUnableToCreatePlanCombinationWithExistingTag()
    {
        $this->expectExceptionMessage('UNIQUE constraint failed');
        $this->testPlanBasic->combinations()->create([
            'tag' => 'test-plan-basic-esp-eur-1-year',
            'country' => 'ESP',
            'currency' => 'EUR',
            'price' => 99.99,
            'invoice_period' => 1,
            'invoice_interval' => 'year'
        ]);
    }

    /**
     * Test Plan Combination creation with already existing content in database
     */
    public function testUnableToCreatePlanCombinationWithExistingContent()
    {
        $this->expectExceptionMessage('UNIQUE constraint failed');
        $this->testPlanBasic->combinations()->create([
            'tag' => 'test-2',
            'country' => 'ESP',
            'currency' => 'EUR',
            'price' => 99.99,
            'invoice_period' => 1,
            'invoice_interval' => 'year'
        ]);
    }
}
