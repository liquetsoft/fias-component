<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use Liquetsoft\Fias\Component\Exception\XmlException;

/**
 * Интерфейс для объекта, который создает итератор для указанного файла.
 */
interface XmlReaderProvider
{
    /**
     * Создает и возвращает объект итератора для указанного файла.
     *
     * @throws XmlException
     */
    public function open(\SplFileInfo $file, string $xpath): XmlReaderIterator;
}
