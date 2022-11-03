<?php

namespace App\Enums;

use ReflectionClass;

abstract class Enum
{
    public static function getConstants(): array
    {
        return (new ReflectionClass(get_called_class()))->getConstants();
    }

    public static function getKeys(): array
    {
        return array_keys(self::getConstants());
    }

    public static function values(): array
    {
        return array_values(self::getConstants());
    }

    public static function getKey($value) : string
    {
        return array_flip(self::getConstants())[$value];
    }
}
