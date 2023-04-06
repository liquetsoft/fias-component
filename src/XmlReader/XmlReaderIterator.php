<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use Iterator;

/**
 * Интерфейс для итератора который читает данные из xml файла.
 *
 * @template-extends Iterator<int, string>
 */
interface XmlReaderIterator extends \Iterator
{
    /**
     * Закрывает открытый файл.
     */
    public function close(): void;
}
