<?php

namespace Bpuig\Subby\Tests\Services\PaymentMethods;

use Bpuig\Subby\Contracts\PaymentMethodService;

class FailedPaymentMethod implements PaymentMethodService
{
    /**
     * Charge desired amount
     * @return void
     */
    public function charge()
    {
        throw new \Exception('Payment failed');
    }
}
