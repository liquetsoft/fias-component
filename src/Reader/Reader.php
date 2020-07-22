<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Reader;

use InvalidArgumentException;
use Iterator;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\Exception;
use SplFileInfo;

/**
 * Интерфейс чтения данных из файла.
 */
interface Reader extends Iterator
{
    /**
     * Открывает файл на чтение, пытается найти указанный путь, если
     * путь найден, то открывает файл и возвращает правду, если не найден, то
     * возвращает ложь.
     *
     * @param SplFileInfo      $file
     * @param EntityDescriptor $entity_descriptor
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function open(SplFileInfo $file, EntityDescriptor $entity_descriptor): bool;

    /**
     * Закрывает открытый файл, если такой был.
     *
     * @return void
     */
    public function close(): void;

    /**
     * Возвращает тип объекта Reader.
     *
     * @return string
     */
    public function getType(): string;
}
