<?php


namespace Bpuig\Subby\Exceptions;


use Throwable;

class PlanSubscriptionFeatureUsageDenied extends \InvalidArgumentException
{
    public function __construct($featureTag = "", $code = 0, Throwable $previous = null)
    {
        $message = "Usage of feature '{$featureTag}' has been denied.";

        parent::__construct($message, $code, $previous);
    }
}
