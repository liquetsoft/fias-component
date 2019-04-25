<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use SplFileInfo;
use Iterator;
use Liquetsoft\Fias\Component\Exception\XmlException;
use InvalidArgumentException;

/**
 * Интерфейс для объекта, который читает данные из xml файла.
 */
interface XmlReader extends Iterator
{
    /**
     * Открывает файл на чтение, пытается найти указанный путь, если
     * путь найден, то открывает файл и возвращает правду, если не найден, то
     * возвращает ложь.
     *
     * @param SplFileInfo $file
     * @param string      $xpath
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     * @throws XmlException
     */
    public function open(SplFileInfo $file, string $xpath): bool;

    /**
     * Закрывает открытый файл, если такой был.
     *
     * @return void
     */
    public function close(): void;
}
