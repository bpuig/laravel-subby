<?php
declare(strict_types=1);

namespace Bpuig\Subby\Traits;
use Illuminate\Database\Eloquent\Builder;

trait IsEventable
{
    /**
     * Get all of the entity events
     */
    public function events()
    {
        return $this->morphMany(config('subby.models.plan_subscription_event'), 'eventable');
    }

    /**
     * Without processed events
     * @param $query
     *
     * @return mixed
     */
    public function scopeUnprocessed($query)
    {
        return $query->where(function (Builder $query) {
            $query->orDoesntHave('events')
                ->orWhereHas('events', function (Builder $query) {
                    $query->whereNull('succeeded_at')
                        ->whereNull('failed_at');
                });
        });
    }
}
