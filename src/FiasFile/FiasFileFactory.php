<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFile;

/**
 * Фабрика, которая может создавать сущности, описывающие файлы.
 */
final class FiasFileFactory
{
    private function __construct()
    {
    }

    /**
     * Создает сущность из описания.
     */
    public static function create(string $name, int $size): FiasFile
    {
        return new FiasFileImpl($name, $size);
    }

    /**
     * Создает сущность из описания для zip архива.
     */
    public static function createFromSplFileInfo(\SplFileInfo $file): FiasFile
    {
        return new FiasFileImpl($file->getPathname(), $file->getSize());
    }
}
