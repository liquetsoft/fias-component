<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasFileSelector;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorArchive;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который выбирает файлы из архива
 * для последующей обработки.
 *
 * @internal
 */
final class FiasFileSelectorArchiveTest extends BaseCase
{
    /**
     * Проверяет, что объект определит подходит ли источник данных.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSupportSource')]
    public function testSupportSource(bool $isArchive): void
    {
        $source = $this->mock(\SplFileInfo::class);
        $entityManager = $this->createEntityManagerMock();

        $unpacker = $this->mock(Unpacker::class);
        $unpacker->expects($this->any())
            ->method('isArchive')
            ->with(
                $this->identicalTo($source)
            )
            ->willReturn($isArchive);

        $selector = new FiasFileSelectorArchive($unpacker, $entityManager);
        $res = $selector->supportSource($source);

        $this->assertSame($isArchive, $res);
    }

    public static function provideSupportSource(): array
    {
        return [
            'is archive' => [
                true,
            ],
            'is not archive' => [
                false,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет все файлы, которые подходят.
     */
    public function testSelectFiles(): void
    {
        $archive = $this->mock(\SplFileInfo::class);

        $archiveFileName = 'archiveFileName';
        $archiveFilePath = "path/to/{$archiveFileName}";
        $archiveFile = $this->createUnpackerFileMock($archiveFilePath, 10);

        $archiveFile1Name = 'archiveFile1Name';
        $archiveFile1Path = "path/to/{$archiveFile1Name}";
        $archiveFile1 = $this->createUnpackerFileMock($archiveFile1Path, 10);

        $unpacker = $this->createUnpackerMock(
            $archive,
            [
                $archiveFile,
                $archiveFile1,
            ]
        );
        $entityManager = $this->createEntityManagerMock([$archiveFileName], [$archiveFile1Name]);

        $selector = new FiasFileSelectorArchive($unpacker, $entityManager);
        $res = $selector->selectFiles($archive);

        $this->assertSame([$archiveFile, $archiveFile1], $res);
    }

    /**
     * Проверяет, что объект отфильтрует файлы, не привязанные к сущностям.
     */
    public function testSelectFilesNoEntityBound(): void
    {
        $archive = $this->mock(\SplFileInfo::class);

        $archiveFileName = 'archiveFileName';
        $archiveFilePath = "path/to/{$archiveFileName}";
        $archiveFile = $this->createUnpackerFileMock($archiveFilePath, 10);

        $unpacker = $this->createUnpackerMock(
            $archive,
            [
                $archiveFile,
            ]
        );
        $entityManager = $this->createEntityManagerMock();

        $selector = new FiasFileSelectorArchive($unpacker, $entityManager);
        $res = $selector->selectFiles($archive);

        $this->assertSame([], $res);
    }

    /**
     * Проверяет, что объект отфильтрует пустые файлы.
     */
    public function testSelectFilesZeroSize(): void
    {
        $archive = $this->mock(\SplFileInfo::class);

        $archiveFileName = 'archiveFileName';
        $archiveFilePath = "path/to/{$archiveFileName}";
        $archiveFile = $this->createUnpackerFileMock($archiveFilePath, 0);

        $unpacker = $this->createUnpackerMock(
            $archive,
            [
                $archiveFile,
            ]
        );
        $entityManager = $this->createEntityManagerMock([$archiveFileName]);

        $selector = new FiasFileSelectorArchive($unpacker, $entityManager);
        $res = $selector->selectFiles($archive);

        $this->assertSame([], $res);
    }

    /**
     * Проверяет, что объект отфильтрует файлы, используя объект фильтра.
     */
    public function testSelectFilesFilter(): void
    {
        $archive = $this->mock(\SplFileInfo::class);

        $archiveFileName = 'archiveFileName';
        $archiveFilePath = "path/to/{$archiveFileName}";
        $archiveFile = $this->createUnpackerFileMock($archiveFilePath, 10);

        $archiveFile1Name = 'archiveFile1Name';
        $archiveFile1Path = "path/to/{$archiveFile1Name}";
        $archiveFile1 = $this->createUnpackerFileMock($archiveFile1Path, 10);

        $unpacker = $this->createUnpackerMock(
            $archive,
            [
                $archiveFile,
                $archiveFile1,
            ]
        );

        $entityManager = $this->createEntityManagerMock([$archiveFileName, $archiveFile1Name]);

        $filter = $this->mock(Filter::class);
        $filter->expects($this->any())
            ->method('test')
            ->willReturnCallback(
                fn (mixed $t): bool => $t === $archiveFile1
            );

        $selector = new FiasFileSelectorArchive($unpacker, $entityManager, $filter);
        $res = $selector->selectFiles($archive);

        $this->assertSame([$archiveFile1], $res);
    }

    /**
     * @return UnpackerFile&MockObject
     */
    private function createUnpackerFileMock(string $name = '', int $size = 0): UnpackerFile
    {
        $file = $this->mock(UnpackerFile::class);
        $file->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $file->expects($this->any())
            ->method('getSize')
            ->willReturn($size);

        return $file;
    }

    /**
     * @return Unpacker&MockObject
     */
    private function createUnpackerMock(?\SplFileInfo $archive = null, array $files = []): Unpacker
    {
        $unpacker = $this->mock(Unpacker::class);
        $unpacker->expects($this->any())
            ->method('isArchive')
            ->willReturnCallback(
                fn (\SplFileInfo $t): bool => $archive && $t === $archive
            );
        $unpacker->expects($this->any())
            ->method('getListOfFiles')
            ->willReturnCallback(
                fn (\SplFileInfo $t): array => $archive && $t === $archive ? $files : []
            );

        return $unpacker;
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
