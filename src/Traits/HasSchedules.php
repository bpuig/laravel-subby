<?php
declare(strict_types=1);

namespace Bpuig\Subby\Traits;

use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Models\PlanCombination;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait HasSchedules
 */
trait HasSchedules
{
    /**
     * Plan to which subscription will change
     * @var
     */
    private $scheduledPlan;

    /**
     * Date on which subscription will be changed
     * @var
     */
    private $scheduledDate;

    /**
     * Method of subscription change creation / update
     * @var string
     */
    private $method = 'create';

    /**
     * The subscription can be scheduled
     * @return HasMany
     */
    public function schedules(): hasMany
    {
        return $this->hasMany(config('subby.models.plan_subscription_schedule'), 'subscription_id', 'id');
    }

    /**
     * Future plan
     *
     * @param Plan|PlanCombination $planCombination Plan or PlanCombination that will be the new one
     *
     * @return HasSchedules|\Bpuig\Subby\Models\PlanSubscription
     */
    public function toPlan(Plan|PlanCombination $planCombination): self
    {
        $this->scheduledPlan = $planCombination;

        return $this;
    }

    /**
     * Schedule to time
     *
     * @param Carbon $date
     * @return HasSchedules
     */
    public function onDate(Carbon $date): self
    {
        $this->scheduledDate = $date;

        return $this;
    }

    /**
     * Create schedule in database
     * @throws \Exception
     */
    public function setSchedule()
    {
        $this->validateDate();
        $this->validatePlan();
        $this->validateNeighbourSchedules();

        $subscriptionChange = [
            'subscription_id' => $this->id,
            'scheduled_at' => $this->scheduledDate
        ];

        return $this->scheduledPlan->schedules()->create($subscriptionChange);
    }

    /**
     * Get the latest schedule set to happen before specified date
     * @param Carbon|null $date
     * @return mixed
     */
    public function getLatestSchedule(?Carbon $date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }

        return app(config('subby.models.plan_subscription_schedule'))
            ->pending($date)
            ->where('subscription_id', $this->id)
            ->orderBy('scheduled_at', 'DESC')
            ->first();
    }

    /**
     * Get the first schedule set to happen after specified date
     * @param Carbon|null $date
     * @return mixed
     */
    public function getFirstSchedule(?Carbon $date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }

        return app(config('subby.models.plan_subscription_schedule'))
            ->where('subscription_id', $this->id)
            ->unprocessed()
            ->where('scheduled_at', '>', $date)
            ->orderBy('scheduled_at', 'ASC')
            ->first();
    }

    /**
     * Validate the schedule date
     * @throws \Exception
     */
    private function validateDate()
    {
        if (empty($this->scheduledDate)) {
            throw new \Exception('Scheduled date is empty.', 401);
        }
        if ($this->scheduledDate <= Carbon::now()) {
            throw new \Exception('Schedule cannot be set in the past.', 401);
        }
    }

    /**
     * This validation avoids change to the same plan change consecutively
     * @throws \Exception
     */
    private function validateNeighbourSchedules()
    {
        // Search previous plan change
        $previous = $this->getLatestSchedule($this->scheduledDate);

        if (!is_null($previous) && $this->arePlansEqual($previous->scheduleable, $this->scheduledPlan)) {
            throw new \Exception('Previous plan change is to the same plan.', 401);
        }

        $next = $this->getFirstSchedule($this->scheduledDate);

        if (!is_null($next) && $this->arePlansEqual($next->scheduleable, $this->scheduledPlan)) {
            throw new \Exception('Next plan change is to the same plan.', 401);
        }
    }

    /**
     * Compare essential data to determine if it's the same plan settings
     * @param $one First plan to compare
     * @param $two Second plan to compare
     * @return bool
     */
    private function arePlansEqual($one, $two)
    {
        return ($one->currency === $two->currency
            && $one->price === $two->price
            && $one->invoice_period === $two->invoice_period
            && $one->invoice_interval === $two->invoice_interval);
    }

    /**
     * Validate the scheduled plan
     * @throws \Exception
     */
    private function validatePlan()
    {
        if (empty($this->scheduledPlan)) {
            throw new \Exception('Scheduled plan/combination is empty.', 401);
        }
    }
}
