<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use Iterator;
use Liquetsoft\Fias\Component\Exception\XmlException;

/**
 * Интерфейс для объекта, который читает данные из xml файла.
 *
 * @template-extends Iterator<int, string>
 */
interface XmlReader extends \Iterator
{
    /**
     * Открывает файл на чтение, пытается найти указанный путь.
     *
     * @throws XmlException
     */
    public function open(\SplFileInfo $file, string $xpath): void;

    /**
     * Закрывает открытый файл.
     */
    public function close(): void;
}
