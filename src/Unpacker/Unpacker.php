<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use SplFileInfo;
use Liquetsoft\Fias\Component\Exception\UnpackerException;
use InvalidArgumentException;

/**
 * Интерфейс для объекта, который распаковывает данные из архива.
 */
interface Unpacker
{
    /**
     * Извлекает данные из указанного в первом параметре архива по
     * указанному во втором параметре пути.
     *
     * @param SplFileInfo $source
     * @param SplFileInfo $destination
     *
     * @throws InvalidArgumentException
     * @throws UnpackerException
     */
    public function unpack(SplFileInfo $source, SplFileInfo $destination): void;
}
