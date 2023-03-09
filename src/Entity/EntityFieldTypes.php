<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Entity;

/**
 * Список типов полей.
 */
enum EntityFieldTypes: string
{
    case STRING = 'string';
    case INT = 'int';
    case FLOAT = 'float';
    case BOOL = 'bool';
}
