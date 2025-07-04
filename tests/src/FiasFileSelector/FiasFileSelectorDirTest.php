<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasFileSelector;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorDir;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Marvin255\FileSystemHelper\FileSystemHelper;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который выбирает файлы из папки
 * для последующей обработки.
 *
 * @internal
 */
final class FiasFileSelectorDirTest extends BaseCase
{
    /**
     * Проверяет, что объект определит подходит ли источник данных.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSupportSource')]
    public function testSupportSource(bool $isDir): void
    {
        $source = $this->mock(\SplFileInfo::class);
        $source->expects($this->any())->method('isDir')->willReturn($isDir);

        $entityManager = $this->createEntityManagerMock();
        $fs = $this->createFileSystemHelperMock($source);

        $selector = new FiasFileSelectorDir($entityManager, $fs);
        $res = $selector->supportSource($source);

        $this->assertSame($isDir, $res);
    }

    public static function provideSupportSource(): array
    {
        return [
            'is dir' => [
                true,
            ],
            'is not dir' => [
                false,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет все файлы, которые подходят.
     */
    public function testSelectFiles(): void
    {
        $dir = $this->mock(\SplFileInfo::class);
        $dir->expects($this->any())->method('isDir')->willReturn(true);

        $fileName = 'fileName.txt';
        $filePath = "/path/to/{$fileName}";
        $fileSize = 10;
        $file = $this->createSplFileInfoMock($filePath, $fileSize);

        $file1Name = 'file1Name.txt';
        $file1Path = "/path/to/{$file1Name}";
        $file1Size = 20;
        $file1 = $this->createSplFileInfoMock($file1Path, $file1Size);

        $fs = $this->createFileSystemHelperMock(
            $dir,
            [
                $file,
                $file1,
            ]
        );

        $entityManager = $this->createEntityManagerMock([$fileName], [$file1Name]);

        $selector = new FiasFileSelectorDir($entityManager, $fs);
        $res = $selector->selectFiles($dir);

        $this->assertCount(2, $res);
        $this->assertSame($res[0]->getName(), $filePath);
        $this->assertSame($res[0]->getSize(), $fileSize);
        $this->assertSame($res[1]->getName(), $file1Path);
        $this->assertSame($res[1]->getSize(), $file1Size);
    }

    /**
     * Проверяет, что объект отфильтрует файлы, не привязанные к сущностям.
     */
    public function testSelectFilesNoEntityBound(): void
    {
        $dir = $this->mock(\SplFileInfo::class);
        $dir->expects($this->any())->method('isDir')->willReturn(true);

        $fileName = 'fileName.txt';
        $filePath = "/path/to/{$fileName}";
        $fileSize = 10;
        $file = $this->createSplFileInfoMock($filePath, $fileSize);

        $fs = $this->createFileSystemHelperMock(
            $dir,
            [
                $file,
            ]
        );
        $entityManager = $this->createEntityManagerMock();

        $selector = new FiasFileSelectorDir($entityManager, $fs);
        $res = $selector->selectFiles($dir);

        $this->assertSame([], $res);
    }

    /**
     * Проверяет, что объект отфильтрует пустые файлы.
     */
    public function testSelectFilesZeroSize(): void
    {
        $dir = $this->mock(\SplFileInfo::class);
        $dir->expects($this->any())->method('isDir')->willReturn(true);

        $fileName = 'fileName.txt';
        $filePath = "/path/to/{$fileName}";
        $fileSize = 0;
        $file = $this->createSplFileInfoMock($filePath, $fileSize);

        $fs = $this->createFileSystemHelperMock(
            $dir,
            [
                $file,
            ]
        );
        $entityManager = $this->createEntityManagerMock([$fileName]);

        $selector = new FiasFileSelectorDir($entityManager, $fs);
        $res = $selector->selectFiles($dir);

        $this->assertSame([], $res);
    }

    /**
     * Проверяет, что объект отфильтрует файлы, используя объект фильтра.
     */
    public function testSelectFilesFilter(): void
    {
        $dir = $this->mock(\SplFileInfo::class);
        $dir->expects($this->any())->method('isDir')->willReturn(true);

        $fileName = 'fileName.txt';
        $filePath = "/path/to/{$fileName}";
        $fileSize = 10;
        $file = $this->createSplFileInfoMock($filePath, $fileSize);

        $file1Name = 'file1Name.txt';
        $file1Path = "/path/to/{$file1Name}";
        $file1Size = 20;
        $file1 = $this->createSplFileInfoMock($file1Path, $file1Size);

        $fs = $this->createFileSystemHelperMock(
            $dir,
            [
                $file,
                $file1,
            ]
        );

        $entityManager = $this->createEntityManagerMock([$fileName, $file1Name]);

        $filter = $this->mock(Filter::class);
        $filter->expects($this->any())
            ->method('test')
            ->willReturnCallback(
                fn (mixed $t): bool => $t === $file1
            );

        $selector = new FiasFileSelectorDir($entityManager, $fs, $filter);
        $res = $selector->selectFiles($dir);

        $this->assertCount(1, $res);
        $this->assertSame($res[0]->getName(), $file1Path);
    }

    /**
     * @return \SplFileInfo&MockObject
     */
    private function createSplFileInfoMock(string $name = '', int $size = 0): \SplFileInfo
    {
        $file = $this->mock(\SplFileInfo::class);
        $file->expects($this->any())
            ->method('getPathname')
            ->willReturn($name);
        $file->expects($this->any())
            ->method('getBasename')
            ->willReturn(pathinfo($name, \PATHINFO_BASENAME));
        $file->expects($this->any())
            ->method('getSize')
            ->willReturn($size);
        $file->expects($this->any())
            ->method('isFile')
            ->willReturn(true);

        return $file;
    }

    /**
     * @return FileSystemHelper&MockObject
     */
    private function createFileSystemHelperMock(\SplFileInfo $dir, array $files = []): FileSystemHelper
    {
        $fs = $this->mock(FileSystemHelper::class);
        $fs->expects($this->any())
            ->method('createDirectoryIterator')
            ->willReturnCallback(
                fn (\SplFileInfo $d): iterable => match ($d) {
                    $dir => (new \ArrayObject($files))->getIterator(),
                    default => [],
                }
            );

        return $fs;
    }

    /**
     * @return EntityManager&MockObject
     */
    private function createEntityManagerMock(array $allowedToInsert = [], array $allowedToDelete = []): EntityManager
    {
        $descriptor = $this->mock(EntityDescriptor::class);

        $entityManager = $this->mock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getDescriptorByInsertFile')
            ->willReturnCallback(
                fn (string $file): ?EntityDescriptor => \in_array($file, $allowedToInsert) ? $descriptor : null
            );
        $entityManager->expects($this->any())
            ->method('getDescriptorByDeleteFile')
            ->willReturnCallback(
                fn (string $file): ?EntityDescriptor => \in_array($file, $allowedToDelete) ? $descriptor : null
            );
        $entityManager->expects($this->any())
            ->method('getClassByDescriptor')
            ->willReturnCallback(
                fn (EntityDescriptor $d): ?string => $d === $descriptor ? 'test' : null
            );

        return $entityManager;
    }
}
