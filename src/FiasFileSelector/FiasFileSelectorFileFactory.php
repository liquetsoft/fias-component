<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\Helper\ArrayHelper;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntity;

/**
 * Фабрика, которая создает внутреннее представление файла для диспетчера процессов.
 */
final class FiasFileSelectorFileFactory
{
    private function __construct()
    {
    }

    /**
     * Создает описание для файла, который запакован в архив.
     */
    public static function createFromArchive(\SplFileInfo $archive, UnpackerEntity $file): FiasFileSelectorFile
    {
        return new FiasFileSelectorFileImpl(
            $file->getName(),
            $file->getSize(),
            $archive->getRealPath()
        );
    }

    /**
     * Создает описание для обычного файла.
     */
    public static function createFromFile(\SplFileInfo $file): FiasFileSelectorFile
    {
        return new FiasFileSelectorFileImpl(
            $file->getPathname(),
            $file->getSize()
        );
    }

    /**
     * Создает описание из массива.
     */
    public static function createFromArray(array $data): FiasFileSelectorFile
    {
        return new FiasFileSelectorFileImpl(
            ArrayHelper::extractStringFromArrayByName('path', $data),
            ArrayHelper::extractIntFromArrayByName('size', $data),
            ArrayHelper::extractStringFromArrayByName('pathToArchive', $data) ?: null
        );
    }
}
