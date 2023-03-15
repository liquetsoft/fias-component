<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\Exception\FiasFileSelectorException;

/**
 * Объект, который хранит описание файла для планировщика процессов.
 */
final class FiasFileSelectorFileImpl implements FiasFileSelectorFile
{
    public function __construct(
        private readonly string $path,
        private readonly int $size,
        private readonly ?string $pathToArchive = null
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathToArchive(): string
    {
        if ($this->pathToArchive === null) {
            throw FiasFileSelectorException::create('File is not archived');
        }

        return $this->pathToArchive;
    }

    /**
     * {@inheritdoc}
     */
    public function isArchived(): bool
    {
        return $this->pathToArchive !== null;
    }
}
