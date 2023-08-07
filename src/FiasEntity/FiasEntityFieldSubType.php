<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

/**
 * Список дополнительных типов полей для строк.
 */
enum FiasEntityFieldSubType: string
{
    case DATE = 'date';
    case UUID = 'uuid';
    case NONE = '';

    /**
     * Возвращает основной тип, к которому применим данный.
     */
    public function getBaseType(): ?FiasEntityFieldType
    {
        return match ($this) {
            self::DATE, self::UUID => FiasEntityFieldType::STRING,
            default => null,
        };
    }
}
