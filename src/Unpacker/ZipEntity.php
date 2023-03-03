<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;

/**
 * Объект, который представляет файл внутри архива.
 *
 * @internal
 */
final class ZipEntity
{
    private readonly array $stats;

    public function __construct(mixed $stats)
    {
        if (!\is_array($stats)) {
            throw UnpackerException::create("Can't create stats object from provided source");
        }
        $this->stats = $stats;
    }

    public function isFile(): bool
    {
        return ((int) ($this->stats['crc'] ?? 0)) > 0;
    }

    public function getIndex(): int
    {
        return (int) ($this->stats['index'] ?? 0);
    }

    public function getSize(): int
    {
        return (int) ($this->stats['size'] ?? 0);
    }

    public function getName(): string
    {
        return (string) ($this->stats['name'] ?? '');
    }
}
