<?php
declare(strict_types=1);

namespace Bpuig\Subby\Traits\PlanSubscriptionSchedule;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait IsScheduled
 */
trait IsScheduled
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
     * Service used in job
     * @var string
     */
    private $scheduledService = 'default';

    /**
     * Tries for schedule job
     * @var int
     */
    private $scheduledTries = 1;

    /**
     * Timeout for job
     * @var int
     */
    private $scheduledTimeout = 120;

    /**
     * Allow scheduled limit ignore
     * @var bool
     */
    private $ignoreScheduleLimit = false;

    /**
     * Check if subscription has reached schedule limits.
     *
     * @return bool
     */
    public function reachedScheduleLimit(): bool
    {
        if (is_null(config('subby.schedule.schedules_per_subscription'))) {
            return false;
        } else {
            return $this->schedules()->notProcessed()->count() >= config('subby.schedule.schedules_per_subscription');
        }
    }

    /**
     * The subscription can be scheduled
     * @return HasMany
     */
    public function schedules(): hasMany
    {
        return $this->hasMany(config('subby.schedule.models.plan_subscription_schedule'), 'subscription_id', 'id');
    }

    /**
     * Future plan
     *
     * @param $plan
     *
     * @return $this
     */
    public function toPlan($plan): self
    {
        $this->scheduledPlan = $plan;

        return $this;
    }

    /**
     * Service used in the processing
     *
     * @param $service
     * @return $this
     */
    public function service(string $service): self
    {
        $this->scheduledService = $service;

        return $this;
    }

    /**
     * Timeout for the job
     *
     * @param int $seconds
     * @return $this
     */
    public function timeout(int $seconds): self
    {
        $this->scheduledTimeout = $seconds;

        return $this;
    }

    /**
     * Tries for the job
     *
     * @param int $number
     * @return $this
     */
    public function tries(int $number): self
    {
        $this->scheduledTries = $number;

        return $this;
    }

    /**
     * Schedule to time
     *
     * @param Carbon $date
     * @throws \Exception
     */
    public function onDate(Carbon $date): self
    {
        $this->scheduledDate = $date->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Set to ignore limit
     * @param bool $value Value of ignore
     * @return $this
     */
    public function ignoreLimit($value = true): self
    {
        $this->ignoreScheduleLimit = $value;
        return $this;
    }

    /**
     * Create schedule in database
     * @throws \Exception
     */
    public function setSchedule()
    {
        $this->validateSchedulesPerSubscription();
        $this->validateDate();
        $this->validatePlan();
        $this->validateConsecutiveChange();

        app(config('subby.schedule.models.plan_subscription_schedule'))->create([
            'plan_id' => $this->scheduledPlan->id,
            'subscription_id' => $this->id,
            'service' => $this->scheduledService,
            'tries' => $this->scheduledTries,
            'timeout' => $this->scheduledTimeout,
            'scheduled_at' => $this->scheduledDate
        ]);
    }

    /**
     * This validation avoids change to the same plan change consecutively
     * @throws \Exception
     */
    private function validateConsecutiveChange()
    {
        // Search previous plan change
        $previous = app(config('subby.schedule.models.plan_subscription_schedule'))
            ->where('subscription_id', $this->id)
            ->notProcessed()
            ->where('scheduled_at', '<=', $this->scheduledDate)
            ->orderBy('scheduled_at', 'DESC')
            ->first();

        if (!is_null($previous) && $previous->plan_id === $this->scheduledPlan->id) {
            throw new \Exception('Previous plan change is to the same plan.', 401);
        }

        $next = app(config('subby.schedule.models.plan_subscription_schedule'))
            ->where('subscription_id', $this->id)
            ->notProcessed()
            ->where('scheduled_at', '>=', $this->scheduledDate)
            ->orderBy('scheduled_at', 'ASC')
            ->first();

        if (!is_null($next) && $next->plan_id === $this->scheduledPlan->id) {
            throw new \Exception('Next plan change is to the same plan.', 401);
        }
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
     * Validate the scheduled plan
     * @throws \Exception
     */
    private function validatePlan()
    {
        if (empty($this->scheduledPlan)) {
            throw new \Exception('Scheduled plan is empty.', 401);
        }

        $model = app(config('subby.models.plan'));
        if ($this->scheduledPlan instanceof $model == false) {
            throw new \Exception('Plan is not a valid Eloquent Plan Model instance.', 401);
        }
    }

    /**
     * Validate if has reached limit of schedules
     * @throws \Exception
     */
    private function validateSchedulesPerSubscription()
    {
        if ($this->ignoreScheduleLimit === false && $this->reachedScheduleLimit()) {
            throw new \Exception('Subscription has reached it\'s schedules limit.', 500);
        }
    }
}
