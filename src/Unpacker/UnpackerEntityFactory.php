<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Helper\ArrayHelper;

/**
 * Фабрика, которая может создавать сущности, описывающие файлы в архиве.
 */
final class UnpackerEntityFactory
{
    private function __construct()
    {
    }

    /**
     * Создает сущность из описания для zip архива.
     */
    public static function createFromZipStats(mixed $stats): UnpackerEntity
    {
        if (!\is_array($stats)) {
            throw UnpackerException::create('Stats must be an array instance');
        }

        $crc = ArrayHelper::extractIntFromArrayByName('crc', $stats);
        $index = ArrayHelper::extractIntFromArrayByName('index', $stats);
        $size = ArrayHelper::extractIntFromArrayByName('size', $stats);
        $name = ArrayHelper::extractStringFromArrayByName('name', $stats);

        return new UnpackerEntityImpl(
            $crc !== 0 ? UnpackerEntityType::FILE : UnpackerEntityType::DIRECTORY,
            $name,
            $index,
            $size
        );
    }
}
