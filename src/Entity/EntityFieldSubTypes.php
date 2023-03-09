<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Entity;

/**
 * Список дополнительных типов полей для строк.
 */
enum EntityFieldSubTypes: string
{
    case DATE = 'date';
    case UUID = 'uuid';
    case NONE = '';

    /**
     * Возвращает основной тип, к которому применим данный.
     */
    public function getBaseType(): ?EntityFieldTypes
    {
        return match ($this) {
            self::DATE, self::UUID => EntityFieldTypes::STRING,
            default => null,
        };
    }
}
