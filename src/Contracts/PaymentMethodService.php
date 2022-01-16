<?php


namespace Bpuig\Subby\Contracts;


interface PaymentMethodService
{
    const TRIES = 3;
    const TIMEOUT = 120;

    /**
     * Logic for charging the payment amount
     */
    public function charge();
}
