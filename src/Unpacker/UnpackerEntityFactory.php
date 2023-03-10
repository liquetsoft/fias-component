<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;

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

        $crc = (int) ($stats['crc'] ?? 0);
        $index = (int) ($stats['index'] ?? 0);
        $size = (int) ($stats['size'] ?? 0);
        $name = (string) ($stats['name'] ?? '');

        return new UnpackerEntityImpl(
            $crc !== 0 ? UnpackerEntityType::FILE : UnpackerEntityType::DIRECTORY,
            $name,
            $index,
            $size
        );
    }
}
