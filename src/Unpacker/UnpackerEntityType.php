<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

/**
 * Список типов сущности из архива.
 */
enum UnpackerEntityType: string
{
    case FILE = 'file';
    case DIRECTORY = 'directory';
}
