<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

/**
 * Класс, который содержит функции для работы со строками.
 *
 * @internal
 */
final class StringHelper
{
    private function __construct()
    {
    }

    /**
     * Преобразует входящее значение в строку приведенную к нижнему регистру и обрезанными пробелами.
     */
    public static function normalize(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }
}
