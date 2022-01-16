<?php

declare(strict_types=1);

namespace Bpuig\Subby\Tests\Services\PaymentMethods;

use Bpuig\Subby\Contracts\PaymentMethodService;
use Bpuig\Subby\Traits\IsPaymentMethod;

class SucceededPaymentMethod implements PaymentMethodService
{
    use IsPaymentMethod;

    /**
     * Charge desired amount
     * @return void
     */
    public function charge()
    {
        // Nothing is charged, no exception is raised
    }
}
