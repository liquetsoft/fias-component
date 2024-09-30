<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

/**
 * Список форматов, которые поддерживает сериализатор.
 */
enum FiasSerializerFormat: string
{
    case XML = 'xml';
    case TEST = 'test';

    /**
     * Проверяет, что указанная строка совпадает с текущим форматом.
     */
    public function isEqual(mixed $format): bool
    {
        return \is_string($format) && strtolower(trim($format)) === $this->value;
    }
}
