<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasFileSelector;

use Liquetsoft\Fias\Component\Exception\FiasFileSelectorException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorImpl;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasEntityCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\UnpackerCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который выбирает файлы для обработки из указанного источника.
 *
 * @internal
 */
class FiasFileSelectorImplTest extends BaseCase
{
    use UnpackerCase;
    use FileSystemCase;
    use FiasEntityCase;

    /**
     * Проверяет, что объект правильно составит список,
     * если в качестве источника указан файл.
     */
    public function testSelectFile(): void
    {
        $sourceName = '/test/test.xml';
        $source = $this->createSplFileInfoMock($sourceName, 10);

        $entity = $this->createFiasEntityMock();
        $entity->method('isFileNameFitsXmlInsertFileMask')->willReturnCallback(
            fn (string $path): bool => $path === $sourceName
        );
        $binder = $this->createFiasEntityBinderMockWithList([$entity]);

        $unpacker = $this->createUnpackerMock();
        $fs = $this->createFileSystemMock();

        $selector = new FiasFileSelectorImpl($binder, $unpacker, $fs);
        $selectedFiles = $selector->select($source);

        $this->assertCount(1, $selectedFiles);
        $this->assertSame($sourceName, $selectedFiles[0]->getPath());
    }

    /**
     * Проверяет, что объект правильно составит список,
     * если в качестве источника указан архив.
     */
    public function testSelectArchive(): void
    {
        $pathToArchive = 'test/archive.zip';
        $source = $this->createSplFileInfoMock($pathToArchive);

        $archivedFiles = [
            'test/file.txt',
            'test/file1.txt',
            'test/file2.txt',
        ];
        $unpacker = $this->createUnpackerFileListMock($source, $archivedFiles);

        $entity = $this->createFiasEntityMock();
        $entity->method('isFileNameFitsXmlInsertFileMask')->willReturnCallback(
            fn (string $path): bool => $path === $archivedFiles[0]
        );
        $entity->method('isFileNameFitsXmlDeleteFileMask')->willReturnCallback(
            fn (string $path): bool => $path === $archivedFiles[1]
        );
        $binder = $this->createFiasEntityBinderMockWithList([$entity]);

        $fs = $this->createFileSystemMock();

        $selector = new FiasFileSelectorImpl($binder, $unpacker, $fs);
        $selectedFiles = $selector->select($source);

        $this->assertCount(2, $selectedFiles);
        $this->assertSame($archivedFiles[0], $selectedFiles[0]->getPath());
        $this->assertSame($pathToArchive, $selectedFiles[0]->getPathToArchive());
        $this->assertSame($archivedFiles[1], $selectedFiles[1]->getPath());
        $this->assertSame($pathToArchive, $selectedFiles[1]->getPathToArchive());
    }

    /**
     * Проверяет, что объект правильно составит список,
     * если в качестве источника указана папка.
     */
    public function testSelectDir(): void
    {
        $source = $this->createSplDirInfoMock();

        $fileNames = [
            'test/file.txt',
            'test/file1.txt',
            'test/file2.txt',
            'test/file3.txt',
        ];

        $entity = $this->createFiasEntityMock();
        $entity->method('isFileNameFitsXmlInsertFileMask')->willReturnCallback(
            fn (string $path): bool => $path === $fileNames[0]
        );
        $entity->method('isFileNameFitsXmlDeleteFileMask')->willReturnCallback(
            fn (string $path): bool => $path === $fileNames[1]
        );

        $entity1 = $this->createFiasEntityMock();
        $entity1->method('isFileNameFitsXmlInsertFileMask')->willReturnCallback(
            fn (string $path): bool => $path === $fileNames[3]
        );
        $entity1->method('isFileNameFitsXmlDeleteFileMask')->willReturnCallback(
            fn (string $path): bool => match ($path) {
                $fileNames[0], $fileNames[1], $fileNames[3] => throw new \RuntimeException('This psth should be proceed by another entity'),
                default => false
            }
        );

        $binder = $this->createFiasEntityBinderMockWithList([$entity, $entity1]);

        $dirFiles = [
            $this->createSplFileInfoMock($fileNames[0], 1),
            $this->createSplFileInfoMock($fileNames[1], 2),
            $this->createSplFileInfoMock($fileNames[2], 3),
            $this->createSplFileInfoMock($fileNames[3], 0),
        ];
        $fs = $this->createFileSystemMock();
        $fs->method('createDirectoryIterator')
            ->with($this->identicalTo($source))
            ->willReturn((new \ArrayObject($dirFiles))->getIterator());

        $unpacker = $this->createUnpackerMock();

        $selector = new FiasFileSelectorImpl($binder, $unpacker, $fs);
        $selectedFiles = $selector->select($source);

        $this->assertCount(2, $selectedFiles);
        $this->assertSame($fileNames[0], $selectedFiles[0]->getPath());
        $this->assertSame($fileNames[1], $selectedFiles[1]->getPath());
    }

    /**
     * Проверяет, что объект правильно составит список,
     * если в качестве источника указана папка и задан фильтр.
     */
    public function testSelectDirWithFilter(): void
    {
        $source = $this->createSplDirInfoMock();

        $fileNames = [
            'test/file.txt',
            'test/file1.txt',
            'test/file2.txt',
            'test/file3.txt',
        ];
        $entity = $this->createFiasEntityMock();
        $entity->method('isFileNameFitsXmlInsertFileMask')->willReturnCallback(
            fn (string $path): bool => $path === $fileNames[0]
        );
        $entity->method('isFileNameFitsXmlDeleteFileMask')->willReturnCallback(
            fn (string $path): bool => $path === $fileNames[1] || $path === $fileNames[3]
        );
        $binder = $this->createFiasEntityBinderMockWithList([$entity]);

        $dirFiles = [
            $this->createSplFileInfoMock($fileNames[0], 1),
            $this->createSplFileInfoMock($fileNames[1], 2),
            $this->createSplFileInfoMock($fileNames[2], 3),
            $this->createSplFileInfoMock($fileNames[3], 4),
        ];
        $fs = $this->createFileSystemMock();
        $fs->method('createDirectoryIterator')
            ->with($this->identicalTo($source))
            ->willReturn((new \ArrayObject($dirFiles))->getIterator());

        $unpacker = $this->createUnpackerMock();

        /** @var Filter&MockObject */
        $filter = $this->getMockBuilder(Filter::class)->getMock();
        $filter->method('test')->willReturnCallback(
            fn (mixed $test): bool => $test === $fileNames[0] || $test === $fileNames[3]
        );

        $selector = new FiasFileSelectorImpl($binder, $unpacker, $fs, $filter);
        $selectedFiles = $selector->select($source);

        $this->assertCount(2, $selectedFiles);
        $this->assertSame($fileNames[0], $selectedFiles[0]->getPath());
        $this->assertSame($fileNames[3], $selectedFiles[1]->getPath());
    }

    /**
     * Проверяет, что объект выбросит исключение, если источник нельзя прочитать.
     */
    public function testSelectCantReadException(): void
    {
        $source = $this->createSplUnreadableInfoMock();
        $binder = $this->createFiasEntityBinderMock();
        $fs = $this->createFileSystemMock();
        $unpacker = $this->createUnpackerMock();

        $selector = new FiasFileSelectorImpl($binder, $unpacker, $fs);

        $this->expectException(FiasFileSelectorException::class);
        $this->expectExceptionMessage("doesn't exist or isn't readable");
        $selector->select($source);
    }
}
