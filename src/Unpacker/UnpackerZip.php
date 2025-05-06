<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Helper\ArrayHelper;

/**
 * Объект, который распаковывает файлы из zip архива.
 */
final class UnpackerZip implements Unpacker
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function unpack(\SplFileInfo $archive, \SplFileInfo $destination): \SplFileInfo
    {
        $callback = function (\ZipArchive $archiveHandler) use ($archive, $destination): \SplFileInfo {
            $res = $archiveHandler->extractTo($destination->getPathName());

            if ($res !== true) {
                throw UnpackerException::create(
                    "Can't unpack archive '%s' to '%s'",
                    $archive->getPathname(),
                    $destination->getPathname()
                );
            }

            return new \SplFileInfo($destination->getRealPath());
        };

        return $this->runInZipContext($archive, $callback);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function unpackFile(\SplFileInfo $archive, string $fileName, \SplFileInfo $destination): \SplFileInfo
    {
        $callback = function (\ZipArchive $archiveHandler) use ($archive, $fileName, $destination): \SplFileInfo {
            $res = $archiveHandler->extractTo($destination->getPathname(), $fileName);

            if ($res !== true) {
                throw UnpackerException::create(
                    "Can't extract entity '%s' form archive '%s'",
                    $fileName,
                    $archive->getPathname()
                );
            }

            return new \SplFileInfo($destination->getPathname() . '/' . $fileName);
        };

        return $this->runInZipContext($archive, $callback);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getListOfFiles(\SplFileInfo $archive): iterable
    {
        $archiveHandler = $this->openArchive($archive);

        for ($i = 0; $i < $archiveHandler->numFiles; ++$i) {
            $stats = $archiveHandler->statIndex($i);
            if (\is_array($stats) && ArrayHelper::extractIntFromArrayByName('crc', $stats) !== 0) {
                yield UnpackerFileFactory::createFromZipStats($archive, $stats);
            }
        }

        $archiveHandler->close();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isArchive(\SplFileInfo $archive): bool
    {
        try {
            return $this->runInZipContext($archive, fn (): bool => true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Запускает коллбэк в контексте открытого архива.
     *
     * @template T
     *
     * @psalm-param callable(\ZipArchive): T $callback
     *
     * @psalm-return T
     */
    private function runInZipContext(\SplFileInfo $path, callable $callback): mixed
    {
        $archive = $this->openArchive($path);

        try {
            $res = $callback($archive);
        } catch (\Throwable $e) {
            throw UnpackerException::wrap($e);
        } finally {
            $archive->close();
        }

        return $res;
    }

    /**
     * Создает обработчик архива и открывает его для чтения.
     */
    private function openArchive(\SplFileInfo $path): \ZipArchive
    {
        $archive = new \ZipArchive();

        $res = $archive->open($path->getPathName(), \ZipArchive::RDONLY);
        if ($res !== true) {
            throw UnpackerException::create(
                "Can't open '%s' archive, error: %s",
                $path->getPathName(),
                $res
            );
        }

        return $archive;
    }
}
