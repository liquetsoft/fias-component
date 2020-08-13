<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Exception\UnpackerException;
use SplFileInfo;

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
     * @param array $files_to_extract
     *
     * @throws InvalidArgumentException
     * @throws UnpackerException
     */
    public function unpack(SplFileInfo $source, SplFileInfo $destination, array $files_to_extract = []): void;
}
