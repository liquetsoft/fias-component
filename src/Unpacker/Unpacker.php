<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;

/**
 * Интерфейс для объекта, который распаковывает данные из архива.
 */
interface Unpacker
{
    /**
     * Извлекает данные из указанного в первом параметре архива по
     * указанному во втором параметре пути.
     *
     * Возвращает полный путь до папки с распакованным содержимым.
     *
     * @throws UnpackerException
     */
    public function unpack(\SplFileInfo $archive, \SplFileInfo $destination): \SplFileInfo;

    /**
     * Извлекает указанный файл или папку в указанную папку назначения и возвращает полный путь.
     *
     * @throws UnpackerException
     */
    public function unpackFile(\SplFileInfo $archive, string $fileName, \SplFileInfo $destination): \SplFileInfo;

    /**
     * Возвращает список файлов, содержащихсяв архиве.
     *
     * @return iterable<UnpackerFile>
     *
     * @throws UnpackerException
     */
    public function getListOfFiles(\SplFileInfo $archive): iterable;

    /**
     * Возвращает правду, если файл является валидным архивом.
     */
    public function isArchive(\SplFileInfo $archive): bool;
}
