<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Helper;

use DateTime;
use Platine\Stdlib\Helper\Str;

/**
 * @class FieldTransformer
 * @package Platine\App\Module\Etl\Helper
 */
class FieldTransformer
{
    /**
     * Put the given parameter to upper case
     * @param string $param
     * @return string
     */
    public static function upper(string $param): string
    {
        return Str::upper($param);
    }

    /**
     * Return the formatted date time
     * @param DateTime|string|int $param
     * @return string
     */
    public static function datetime($param): string
    {
        if ($param instanceof DateTime) {
            return $param->format('Y-m-d H:i:s');
        }

        if (is_int($param)) {
            return date('Y-m-d H:i:s', $param);
        }

        return $param;
    }

    /**
     * Return the formatted date
     * @param DateTime|string|int $param
     * @return string
     */
    public static function date($param): string
    {
        if ($param instanceof DateTime) {
            return $param->format('Y-m-d');
        }

        if (is_int($param)) {
            return date('Y-m-d', $param);
        }

        return $param;
    }

    /**
     * Return the formatted money/amount
     * @param float|string|int $param
     * @return string
     */
    public static function money($param): string
    {
        if (is_numeric($param)) {
            return number_format((float) $param);
        }

        return $param;
    }
}
