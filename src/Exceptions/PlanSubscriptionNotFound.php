<?php


namespace Bpuig\Subby\Exceptions;


use Throwable;

class PlanSubscriptionNotFound extends \InvalidArgumentException
{
    public function __construct($subscriptionTag = "", $code = 0, Throwable $previous = null)
    {
        $message = "Subscription {$subscriptionTag} not found in subscriber.";

        parent::__construct($message, $code, $previous);
    }
}
