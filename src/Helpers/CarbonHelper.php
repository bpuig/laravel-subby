<?php


namespace Bpuig\Subby\Helpers;


class CarbonHelper
{
    /**
     *
     * @param string $action
     * @param string $interval
     * @return string
     */
    public static function actionIn(string $action = 'add', string $interval = 'day')
    {
        return strtolower($action) . ucfirst($interval) . 's';
    }


    /**
     *
     * @param string $action
     * @param string $interval
     * @return string
     */
    public static function diffIn(string $interval = 'day')
    {
        return 'diffIn' . ucfirst($interval) . 's';
    }
}
