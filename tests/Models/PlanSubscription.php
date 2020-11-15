<?php

namespace Bpuig\Subby\Tests\Models;

use Bpuig\Subby\Traits\PlanSubscriptionSchedule\IsScheduled;

class PlanSubscription extends \Bpuig\Subby\Models\PlanSubscription
{
    use IsScheduled;
}
