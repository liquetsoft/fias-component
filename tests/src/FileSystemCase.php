<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Marvin255\FileSystemHelper\FileSystemHelper;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Трэйт, который содержит методы для создания моков файловой системы.
 */
trait FileSystemCase
{
    /**
     * Создает мок для файловой системы.
     *
     * @return FileSystemHelper&MockObject
     */
    public function createFileSystemMock(): FileSystemHelper
    {
        /** @var FileSystemHelper&MockObject */
        $fs = $this->getMockBuilder(FileSystemHelper::class)->getMock();

        return $fs;
    }

    /**
     * Создает мок для файла.
     *
     * @return \SplFileInfo&MockObject
     */
    public function createSplFileInfoMock(string $name = '', int $size = 0): \SplFileInfo
    {
        /** @var \SplFileInfo&MockObject */
        $file = $this->getMockBuilder(\SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file->method('getPathname')->willReturn($name);
        $file->method('getRealPath')->willReturn($name);
        $file->method('getPath')->willReturn($name);
        $file->method('getSize')->willReturn($size);
        $file->method('isFile')->willReturn(true);
        $file->method('isDir')->willReturn(false);

        return $file;
    }

    /**
     * Создает мок для каталога.
     *
     * @return \SplFileInfo&MockObject
     */
    public function createSplDirInfoMock(string $name = ''): \SplFileInfo
    {
        /** @var \SplFileInfo&MockObject */
        $file = $this->getMockBuilder(\SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file->method('getPathname')->willReturn($name);
        $file->method('getRealPath')->willReturn($name);
        $file->method('getPath')->willReturn($name);
        $file->method('isFile')->willReturn(false);
        $file->method('isDir')->willReturn(true);

        return $file;
    }

    /**
     * Создает мок для файла, который невозможно прочитать.
     *
     * @return \SplFileInfo&MockObject
     */
    public function createSplUnreadableInfoMock(): \SplFileInfo
    {
        /** @var \SplFileInfo&MockObject */
        $file = $this->getMockBuilder(\SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file->method('getPathname')->willReturn('');
        $file->method('getRealPath')->willReturn(false);
        $file->method('getPath')->willReturn('');
        $file->method('isFile')->willReturn(false);
        $file->method('isDir')->willReturn(false);

        return $file;
    }

    /**
     * Проверяет, что для указанного пути нет открытых ресурсов.
     */
    public function assertPathHasNoOpenedResources(string $path): void
    {
        $localFileHandler = null;
        foreach (get_resources() as $resource) {
            try {
                $options = stream_get_meta_data($resource);
            } catch (\Throwable $e) {
                $options = [];
            }
            if (isset($options['uri']) && $options['uri'] === $path) {
                $localFileHandler = $resource;
                break;
            }
        }

        $this->assertNull($localFileHandler, 'Local file resource must be closed');
    }
}
