<?php

declare(strict_types=1);

namespace App\Infra\Tools;

class ArrayTools
{
    /**
     * Return true if the callback is true for all the array elements.
     */
    public static function every(array $array, callable $callback): bool
    {
        foreach($array as $value) {
            if (!call_user_func($callback, $value)) return false;
        }
        return true;
    }

    /**
     * Return an array containing only the specified keys (if existing) of the source array.
     */
    public static function pick(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Return true if the callback is true for at least one element of the array.
     */
    public static function some(array $array, callable $callback): bool
    {
        foreach($array as $value) {
            if (call_user_func($callback, $value)) return true;
        }
        return false;
    }
}
