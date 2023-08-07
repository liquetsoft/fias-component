<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\Exception\FiasFileSelectorException;

/**
 * Интерфейс для объекта, который выбирает файлы для обработки из указанного источника.
 */
interface FiasFileSelector
{
    /**
     * Выбирает список файлов из указанного источника для последующей обработки.
     *
     * @return FiasFileSelectorFile[]
     *
     * @throws FiasFileSelectorException
     */
    public function select(\SplFileInfo $source): array;
}
