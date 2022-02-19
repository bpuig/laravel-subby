<?php

namespace Bpuig\Subby\Traits;

trait MorphsSchedules
{
    /**
     * Get all schedules.
     */
    public function schedules()
    {
        return $this->morphMany(config('subby.models.plan_subscription_schedule'), 'scheduleable');
    }
}
