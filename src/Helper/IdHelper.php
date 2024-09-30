<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

/**
 * Класс, который содержит функции для генерации уникальных идентификаторов.
 */
final class IdHelper
{
    private const RANDOM_BYTE_SIZE = 30;

    private function __construct()
    {
    }

    /**
     * Создает строку, содержащую уникальны идентификатор.
     */
    public static function createUniqueId(): string
    {
        return md5(time() . random_bytes(self::RANDOM_BYTE_SIZE));
    }
}
