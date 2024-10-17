<?php
namespace Lukiman\Cores\Utils;

class Data {
    public static function interpolation (int|float $key, int|float $key1, int|float $value1, int|float $key2, int|float $value2) : int|float {
        return $value1 + ($value2 - $value1) * ($key - $key1) / ($key2 - $key1);
    }

    public static function extrapolation (int|float $key, int|float $key1, int|float $value1, int|float $key2, int|float $value2) : int|float {
        return $value1 + ($value2 - $value1) * ($key - $key1) / ($key2 - $key1);
    }
}
