<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

/**
 * Класс, который содержит функции для работы с массивами.
 */
final class ArrayHelper
{
    private function __construct()
    {
    }

    /**
     * Извлекает строку из указанного массива по имени ключа.
     */
    public static function extractStringFromArrayByName(string $name, array $array, string $default = ''): string
    {
        if (!isset($array[$name])) {
            return $default;
        }

        return trim((string) $array[$name]);
    }

    /**
     * Извлекает число из указанного массива по имени ключа.
     */
    public static function extractIntFromArrayByName(string $name, array $array, int $default = 0): int
    {
        if (!isset($array[$name])) {
            return $default;
        }

        return (int) $array[$name];
    }

    /**
     * Извлекает булево значение из указанного массива по имени ключа.
     */
    public static function extractBoolFromArrayByName(string $name, array $array, bool $default = false): bool
    {
        if (!isset($array[$name])) {
            return $default;
        }

        return (bool) $array[$name];
    }

    /**
     * Извлекает массив из указанного массива по имени ключа.
     */
    public static function extractArrayFromArrayByName(string $name, array $array, array $default = []): array
    {
        if (!isset($array[$name])) {
            return $default;
        }

        return (array) $array[$name];
    }
}
