<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Common;

/**
 * Class Functions
 * @package Cratia\ORM\DBAL\Common
 */
class Functions
{
    /**
     * @param $run_time
     * @return string
     */
    public static function pettyRunTime($run_time)
    {
        if ($run_time === 0) {
            return '0 ms';
        }
        if ($run_time < 1) {
            return ceil($run_time * 1000) . ' ms';
        }
        $units = array(
            'hour' => 3600,
            'minute' => 60,
            'second' => 1,
        );
        $s = '';
        foreach ($units as $name => $divisor) {
            if ($quot = intval($run_time / $divisor)) {
                $s .= "$quot $name";
                $s .= (abs($quot) > 1 ? 's' : '') . ', ';
                $run_time -= $quot * $divisor;
            }
        }
        return substr($s, 0, -2);
    }
}