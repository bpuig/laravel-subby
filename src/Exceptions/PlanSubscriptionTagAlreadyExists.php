<?php


namespace Bpuig\Subby\Exceptions;


use Throwable;

class PlanSubscriptionTagAlreadyExists extends \InvalidArgumentException
{
    public function __construct($tag = "", $code = 0, Throwable $previous = null)
    {
        $message = "A subscription with tag '{$tag}' is duplicated for this subscriber.";
        parent::__construct($message, $code, $previous);
    }
}
