<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\Exception\FiasFileSelectorException;

/**
 * Объект, который хранит описание файла для планировщика процессов.
 *
 * @internal
 */
final class FiasFileSelectorFileImpl implements FiasFileSelectorFile
{
    private readonly string $path;
    private readonly int $size;
    private readonly ?string $pathToArchive;

    public function __construct(string $path, int $size, string $pathToArchive = null)
    {
        $this->path = trim($path);
        $this->size = $size;
        $this->pathToArchive = $pathToArchive !== null ? trim($pathToArchive) : null;

        if ($this->path === '') {
            throw FiasFileSelectorException::create("path param can't be empty");
        }

        if ($this->pathToArchive === '') {
            throw FiasFileSelectorException::create("pathToArchive param can't be empty");
        }

        if ($this->size < 0) {
            throw FiasFileSelectorException::create("Size can't be less than 0");
        }
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
    public function getFileName(): string
    {
        return pathinfo($this->path)['basename'] ?? '';
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
