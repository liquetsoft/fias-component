<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use SplFileInfo;
use RarArchive;
use Throwable;
use InvalidArgumentException;

/**
 * Объект, который распаковывает файлы из rar архива.
 */
class RarUnpacker implements Unpacker
{
    /**
     * @inheritdoc
     *
     * @psalm-suppress TooFewArguments
     */
    public function unpack(SplFileInfo $source, SplFileInfo $destination): void
    {
        if (!$source->isFile() || !$source->isReadable()) {
            throw new InvalidArgumentException(
                "Can't find or read archive '" . $source->getPath() . "' to extract."
            );
        }

        if (!$destination->isDir() || !$destination->isWritable()) {
            throw new InvalidArgumentException(
                "Destination folder '" . $destination->getPath() . "' isn't writable or doesn't exist."
            );
        }

        try {
            $archive = $this->getRarInstance($source);
            $this->extractArciveTo($archive, $destination);
        } catch (Throwable $e) {
            $message = "Can't extract '" . $source->getPathname() . "' to '" . $destination->getPathname() . "'.";
            throw new UnpackerException($message, 0, $e);
        }

        $archive->close();
    }

    /**
     * Распаковывает архив в указанный каталог.
     *
     * @param RarArchive  $archive
     * @param SplFileInfo $destination
     *
     * @throws UnpackerException
     *
     * @psalm-suppress TooFewArguments
     */
    protected function extractArciveTo(RarArchive $archive, SplFileInfo $destination): void
    {
        $entries = $archive->getEntries();

        if (!is_array($entries)) {
            throw new UnpackerException("Can't read entries from archive");
        }

        $path = $destination->getPathname();
        foreach ($entries as $entry) {
            if ($entry->extract($path) === false) {
                $name = $entry->getName();
                throw new UnpackerException(
                    "Can't extract entry {$name} to {$path}"
                );
            }
        }
    }

    /**
     * Возвращает объект с открытым архивом.
     *
     * @param SplFileInfo $source
     *
     * @return RarArchive
     *
     * @throws UnpackerException
     */
    protected function getRarInstance(SplFileInfo $source): RarArchive
    {
        $rar = RarArchive::open($source->getPathname());

        if (!($rar instanceof RarArchive)) {
            throw new UnpackerException(
                "Can't open file '" . $source->getPathname() . "' as rar archive"
            );
        }

        return $rar;
    }
}
