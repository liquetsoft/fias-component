<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;

/**
 * DTO для внутреннего представления треда в планировщике.
 *
 * @internal
 */
final class FiasThreadPlannerThread
{
    /**
     * @var FiasFileSelectorFile[]
     */
    private array $files = [];

    private int $size = 0;

    /**
     * @return FiasFileSelectorFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param FiasFileSelectorFile[] $files
     */
    public function addFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    private function addFile(FiasFileSelectorFile $file): void
    {
        $this->files[] = $file;
        $this->size += $file->getSize();
    }
}
