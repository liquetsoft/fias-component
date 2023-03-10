<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

/**
 * Список типов полей.
 */
enum FiasEntityFieldType: string
{
    case STRING = 'string';
    case INT = 'int';
    case FLOAT = 'float';
    case BOOL = 'bool';
}
