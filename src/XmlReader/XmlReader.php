<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use Iterator;
use Liquetsoft\Fias\Component\Exception\XmlException;

/**
 * Интерфейс для объекта, который читает данные из xml файла.
 *
 * @template-extends Iterator<int, string|null>
 */
interface XmlReader extends \Iterator
{
    /**
     * Открывает файл на чтение, пытается найти указанный путь, если
     * путь найден, то открывает файл и возвращает правду, если не найден, то
     * возвращает ложь.
     *
     * @param \SplFileInfo $file
     * @param string       $xpath
     *
     * @return bool
     *
     * @throws XmlException
     */
    public function open(\SplFileInfo $file, string $xpath): bool;

    /**
     * Закрывает открытый файл, если такой был.
     *
     * @return void
     */
    public function close(): void;
}
