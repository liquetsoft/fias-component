<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Helper\ArrayHelper;

/**
 * Фабрика, которая может создавать сущности, описывающие файлы в архиве.
 */
final class UnpackerFileFactory
{
    private function __construct()
    {
    }

    /**
     * Создает сущность из описания для zip архива.
     */
    public static function createFromZipStats(\SplFileInfo $archiveFile, array $stats): UnpackerFile
    {
        return new UnpackerFileImpl(
            $archiveFile,
            ArrayHelper::extractStringFromArrayByName('name', $stats),
            ArrayHelper::extractIntFromArrayByName('index', $stats),
            ArrayHelper::extractIntFromArrayByName('size', $stats)
        );
    }
}
