<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

/**
 * Объект, который представляет файл внутри архива.
 *
 * @internal
 */
final readonly class UnpackerFileImpl implements UnpackerFile
{
    public function __construct(
        private readonly \SplFileInfo $archiveFile,
        private readonly string $name,
        private readonly int $index,
        private readonly int $size,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getArchiveFile(): \SplFileInfo
    {
        return $this->archiveFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(): int
    {
        return $this->index;
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
    public function getName(): string
    {
        return $this->name;
    }
}
