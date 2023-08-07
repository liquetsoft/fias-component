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
     * @throws UnpackerException
     */
    public function unpack(\SplFileInfo $archive, \SplFileInfo $destination): void;

    /**
     * Возвращает список файлов, содержащихсяв архиве.
     *
     * @return UnpackerEntity[]
     *
     * @throws UnpackerException
     */
    public function getListOfFiles(\SplFileInfo $archive): array;

    /**
     * Извлекает указанный файл или папку в указанную папку назначения и возвращает полный путь.
     *
     * @throws UnpackerException
     */
    public function extractEntity(\SplFileInfo $archive, string $entityName, \SplFileInfo $destination): string;

    /**
     * Возвращает правду, если файл является валидным архивом.
     */
    public function isArchive(\SplFileInfo $archive): bool;
}
