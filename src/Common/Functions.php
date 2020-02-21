<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Common;

use DateTime;

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

    /**
     * @param string $sentence
     * @param array $params
     * @return string
     */
    public static function formatSql(string $sentence, array $params): string
    {
        $search = [];
        $replace = [];
        foreach ($params as $name => $value) {
            if (is_string($value) &&
                DateTime::createFromFormat('Y-m-d G:i:s', $value) !== false
            ) {
                $replace[] = "'{$value}'";
            } elseif (is_string($value)) {
                $replace[] = "'{$value}'";
            } elseif (is_numeric($value)) {
                $replace[] = $value;
            } elseif (is_bool($value)) {
                $replace[] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            } elseif (is_null($value)) {
                $replace[] = 'NULL';
            } else {
                $replace[] = $value;
            }

            if (is_numeric($name)) {
                $search[] = "?";
            } else {
                $search[] = ":{$name}";
            }
        }

        foreach ($search as $index => $pattern) {
            $pattern = '/' . preg_quote($pattern, '/') . '/';
            $replacement = $replace[$index];
            $sentence = preg_replace($pattern, $replacement, $sentence, 1);
        }

        return $sentence;
    }
}