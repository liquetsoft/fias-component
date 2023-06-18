<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasFileSelector;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFileFactory;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntity;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для фабрики, которая создает внутреннее представление файла.
 *
 * @internal
 */
class FiasFileSelectorFileFactoryTest extends BaseCase
{
    /**
     * Проверяет, что фабрика создаст объект для файла в архиве.
     */
    public function testCreateFromArchive(): void
    {
        $pathToArchive = '/path/archive.zip';
        $pathToEntity = '/path/entity.txt';
        $size = 10;

        /** @var \SplFileInfo&MockObject */
        $fileInfoArchive = $this->getMockBuilder(\SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileInfoArchive->method('getRealPath')->willReturn($pathToArchive);

        /** @var UnpackerEntity&MockObject */
        $unpackerEntity = $this->getMockBuilder(UnpackerEntity::class)->getMock();
        $unpackerEntity->method('getName')->willReturn($pathToEntity);
        $unpackerEntity->method('getSize')->willReturn($size);

        $file = FiasFileSelectorFileFactory::createFromArchive($fileInfoArchive, $unpackerEntity);
        $resultPath = $file->getPath();
        $resultSize = $file->getSize();
        $resultArchive = $file->getPathToArchive();

        $this->assertSame($pathToEntity, $resultPath);
        $this->assertSame($size, $resultSize);
        $this->assertSame($pathToArchive, $resultArchive);
    }

    /**
     * Проверяет, что фабрика создаст объект из SplFileInfo.
     */
    public function testCreateFromFile(): void
    {
        $path = '/path/test.txt';
        $size = 10;

        /** @var \SplFileInfo&MockObject */
        $fileInfo = $this->getMockBuilder(\SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileInfo->method('getPathname')->willReturn($path);
        $fileInfo->method('getSize')->willReturn($size);

        $file = FiasFileSelectorFileFactory::createFromFile($fileInfo);
        $resultPath = $file->getPath();
        $resultSize = $file->getSize();

        $this->assertSame($path, $resultPath);
        $this->assertSame($size, $resultSize);
    }

    /**
     * Проверяет, что фабрика создаст объект из массива.
     *
     * @dataProvider provideCreateFromArray
     */
    public function testCreateFromArray(array $array, array $awaits): void
    {
        $file = FiasFileSelectorFileFactory::createFromArray($array);

        $this->assertSame(
            $awaits,
            [
                'path' => $file->getPath(),
                'size' => $file->getSize(),
                'pathToArchive' => $file->isArchived() ? $file->getPathToArchive() : null,
            ]
        );
    }

    public function provideCreateFromArray(): array
    {
        return [
            'file' => [
                ['path' => '/path', 'size' => 123, 'test' => 'test'],
                ['path' => '/path', 'size' => 123, 'pathToArchive' => null],
            ],
            'archive' => [
                ['path' => '/path', 'size' => 123, 'pathToArchive' => '/archive', 'test' => 'test'],
                ['path' => '/path', 'size' => 123, 'pathToArchive' => '/archive'],
            ],
            'string size' => [
                ['path' => '/path', 'size' => '123'],
                ['path' => '/path', 'size' => 123, 'pathToArchive' => null],
            ],
            'empty size' => [
                ['path' => '/path'],
                ['path' => '/path', 'size' => 0, 'pathToArchive' => null],
            ],
            'int path' => [
                ['path' => 123, 'size' => 123],
                ['path' => '123', 'size' => 123, 'pathToArchive' => null],
            ],
            'int archive' => [
                ['path' => '/path', 'size' => 123, 'pathToArchive' => 321],
                ['path' => '/path', 'size' => 123, 'pathToArchive' => '321'],
            ],
        ];
    }
}
