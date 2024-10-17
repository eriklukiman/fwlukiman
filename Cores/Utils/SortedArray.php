<?php
namespace Lukiman\Cores\Utils;

class SortedArray {

    public static function getLowerValue(int|float $target, array $array, bool $isInclusive = true) : int|float|null {
        $count = count($array);
        if ($count == 0) return null;
        if ($target == $array[0] AND $isInclusive) return $array[0];
        if ($target <= $array[0]) return null;
        if ($target > $array[$count - 1]) return $array[$count - 1];
        $left = 0;
        $right = $count - 1;
        while ($left < $right) {
            $mid = $left + intdiv($right - $left, 2);
            if ($array[$mid] == $target) return $isInclusive ? $array[$mid] : $array[$mid - 1];
            if ($array[$mid] < $target) $left = $mid + 1;
            else $right = $mid;
        }
        if ($isInclusive AND $array[$left] == $target) return $array[$left];
        return $left == 0 ? null : $array[$left - 1];
    }

    public static function getHigherValue(int|float $target, array $array, bool $isInclusive = true) : int|float|null {
        $count = count($array);
        if ($count == 0) return null;
        if ($target == $array[$count - 1] AND $isInclusive) return $array[$count - 1];
        if ($target >= $array[$count - 1]) return null;
        if ($target < $array[0]) return $array[0];
        $left = 0;
        $right = $count - 1;
        while ($left < $right) {
            $mid = $left + intdiv($right - $left, 2);
            if ($array[$mid] == $target) return $isInclusive ? $array[$mid] : $array[$mid + 1];
            if ($array[$mid] < $target) $left = $mid + 1;
            else $right = $mid;
        }
        if ($isInclusive AND $array[$left] == $target) return $array[$left];
        return $left == 0 ? null : $array[$left];
    }


}
