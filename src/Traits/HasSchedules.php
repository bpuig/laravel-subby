<?php
declare(strict_types=1);

namespace Bpuig\Subby\Traits;

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
     * @param string $service
     * @return $this
     */
    public function usingService(string $service): self
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
            'plan_id' => $this->scheduledPlan->id,
            'subscription_id' => $this->id,
            'service' => $this->scheduledService,
            'tries' => $this->scheduledTries,
            'timeout' => $this->scheduledTimeout,
            'scheduled_at' => $this->scheduledDate
        ];

        return app(config('subby.models.plan_subscription_schedule'))->create($subscriptionChange);
    }

    /**
     * Get the latest schedule set to happen before specified date
     * @param Carbon|null $date
     * @return mixed
     */
    public function getLatestSchedule(?Carbon $date = null) {
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
    public function getFirstSchedule(?Carbon $date = null) {
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

        if (!is_null($previous) && $previous->plan_id === $this->scheduledPlan->id) {
            throw new \Exception('Previous plan change is to the same plan.', 401);
        }

        $next = $this->getFirstSchedule($this->scheduledDate);

        if (!is_null($next) && $next->plan_id === $this->scheduledPlan->id) {
            throw new \Exception('Next plan change is to the same plan.', 401);
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
}
