<?php


namespace Bpuig\Subby\Traits;


trait IsScheduleService
{
    private $planSubscriptionSchedule;

    private $clearUsage = true;
    private $syncInvoicing = true;

    /**
     * Service action outcome
     * @var bool
     */
    private $success = false;

    /**
     * Change the plan or throw exception
     * @throws \Exception
     */
    public function changePlan()
    {
        if ($this->success) {
            $this->planSubscriptionSchedule->executeChange($this->clearUsage, $this->syncInvoicing);
        } else {
            $this->planSubscriptionSchedule->fail();
            throw new \Exception('Process failed.', 500);
        }
    }
}
