<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;

/**
 * Объект, который распаковывает файлы из zip архива.
 */
final class ZipUnpacker implements Unpacker
{
    /**
     * {@inheritDoc}
     */
    public function unpack(\SplFileInfo $source, \SplFileInfo $destination): void
    {
        try {
            $this->runUnZip($source, $destination);
        } catch (\Throwable $e) {
            throw UnpackerException::wrap($e);
        }
    }

    /**
     * Распаковывает архив в указанную папку.
     *
     * @throws UnpackerException
     */
    private function runUnZip(\SplFileInfo $source, \SplFileInfo $destination): void
    {
        $filePath = $source->getPathName();
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $zip->extractTo($destination->getPathName());
            $zip->close();
        } else {
            throw UnpackerException::create("Can't open '%s' zip archive file.", $filePath);
        }
    }
}
