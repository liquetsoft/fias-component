<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntity;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntityType;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Трэйт, который содержит методы для создания моков распаковщика.
 */
trait UnpackerCase
{
    /**
     * Создает мок для распаковщика, который может извлечь список указанных файлов.
     *
     * @param string[] $files
     */
    public function createUnpackerFileListMock(\SplFileInfo $archive, array $files): Unpacker
    {
        $unpacker = $this->createUnpackerMock();

        $unpacker->method('isArchive')->willReturnCallback(
            fn (\SplFileInfo $item): bool => $item === $archive
        );

        $entites = [];
        foreach ($files as $fileName) {
            $entites[] = $this->createUnpackerEntityMock($fileName, 10);
        }
        $unpacker->method('getListOfFiles')->willReturnCallback(
            fn (\SplFileInfo $item): array => $item === $archive ? $entites : []
        );

        return $unpacker;
    }

    /**
     * Создает мок для распаковщика.
     *
     * @return Unpacker&MockObject
     */
    public function createUnpackerMock(): Unpacker
    {
        /** @var Unpacker&MockObject */
        $unpacker = $this->getMockBuilder(Unpacker::class)->getMock();

        return $unpacker;
    }

    /**
     * Создает мок для распаковщика.
     *
     * @return UnpackerEntity&MockObject
     */
    public function createUnpackerEntityMock(string $name, int $size = 0, UnpackerEntityType $type = UnpackerEntityType::FILE): UnpackerEntity
    {
        /** @var UnpackerEntity&MockObject */
        $entity = $this->getMockBuilder(UnpackerEntity::class)->getMock();

        $entity->method('getType')->willReturn($type);
        $entity->method('getSize')->willReturn($size);
        $entity->method('getName')->willReturn($name);

        return $entity;
    }
}
