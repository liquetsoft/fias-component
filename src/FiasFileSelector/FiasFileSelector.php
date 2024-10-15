<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\FiasFile\FiasFile;

/**
 * Интерфейс для объекта, который выбирает файлы из архива
 * для последующей обработки без распаковки самого архива.
 */
interface FiasFileSelector
{
    /**
     * Выбирает файлы из архива для последующей обработки.
     *
     * @return FiasFile[]
     */
    public function selectFiles(\SplFileInfo $source): array;
}
