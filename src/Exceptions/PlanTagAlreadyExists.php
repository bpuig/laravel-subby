<?php


namespace Bpuig\Subby\Exceptions;


use Throwable;

class PlanTagAlreadyExists extends \InvalidArgumentException
{
    public function __construct($planTag = "", $code = 0, Throwable $previous = null)
    {
        $message = "Plan tag {$planTag} already exists.";

        parent::__construct($message, $code, $previous);
    }
}
