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
     * @throws \InvalidArgumentException
     * @throws UnpackerException
     */
    public function unpack(\SplFileInfo $source, \SplFileInfo $destination): void;
}
