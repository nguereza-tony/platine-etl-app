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
     * @param mixed|string $format the date format
     * @return string
     */
    public static function datetime($param, $format = 'Y-m-d H:i:s'): string
    {
        if ($param instanceof DateTime) {
            return $param->format($format);
        }

        if (is_int($param)) {
            return date($format, $param);
        }

        return $param;
    }

    /**
     * Return the formatted date
     * @param DateTime|string|int $param
     * @param mixed|string $format the date format
     * @return string
     */
    public static function date($param, $format = 'Y-m-d'): string
    {
        if ($param instanceof DateTime) {
            return $param->format($format);
        }

        if (is_int($param)) {
            return date($format, $param);
        }

        return $param;
    }

    /**
     * Return the formatted money/amount
     * @param float|string|int $param
     * @param int|mixed $decimal
     * @param string|mixed $decimalSeparator
     * @param string|mixed $separator
     * @return string
     */
    public static function money(
        $param,
        $decimal = 2,
        $decimalSeparator = '.',
        $separator = ','
    ): string {
        if (is_numeric($param)) {
            return number_format(
                (float) $param,
                (int) $decimal,
                (string)$decimalSeparator,
                (string)$separator
            );
        }

        return $param;
    }
}
