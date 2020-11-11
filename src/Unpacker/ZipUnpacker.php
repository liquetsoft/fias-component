<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use RuntimeException;
use SplFileInfo;
use Throwable;
use ZipArchive;

/**
 * Объект, который распаковывает файлы из zip архива.
 */
class ZipUnpacker implements Unpacker
{
    /**
     * @inheritDoc
     */
    public function unpack(SplFileInfo $source, SplFileInfo $destination, $filesToExtract = []): void
    {
        try {
            $this->runUnZip($source, $destination, $filesToExtract);
        } catch (Throwable $e) {
            $message = "Can't extract zip archive '{$source->getPathname()}' to '{$destination->getPathname()}'.";
            throw new UnpackerException($message, 0, $e);
        }
    }

    /**
     * Фильтрует и распаковывает файлы в указанную папку.
     *
     * @param SplFileInfo $source
     * @param SplFileInfo $destination
     * @param array $filesToExtract
     *
     * @throws RuntimeException
     */
    private function runUnZip(SplFileInfo $source, SplFileInfo $destination, array $filesToExtract = []): void
    {
        $filePath = $source->getPathName();
        $zip = new ZipArchive;
        if ($zip->open($filePath) === true) {
            $entries = [];
            for ($i = 0; $i < $zip->count(); $i++) {
                $fileName = $zip->getNameIndex($i);
                if (in_array($fileName, $filesToExtract)) {
                    $entries[] = $fileName;
                }
            }
            if (!empty($entries)) {
                $zip->extractTo($destination->getPathName(), $entries);
            } else {
                $zip->extractTo($destination->getPathName());
            }
            $zip->extractTo($destination->getPathName(), $entries);
            $zip->close();
        } else {
            throw new RuntimeException(
                sprintf("Can't open '%s' zip archive file.", $filePath)
            );
        }
    }
}
