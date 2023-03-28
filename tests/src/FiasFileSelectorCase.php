<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Трэйт, содержащий моки для файлов, подходящих под загрузку.
 */
trait FiasFileSelectorCase
{
    /**
     * Создает мок для FiasFileSelectorFile.
     *
     * @return FiasFileSelectorFile&MockObject
     */
    public function createFiasFileSelectorFileMock(string $path = '', int $size = 0): FiasFileSelectorFile
    {
        /** @var FiasFileSelectorFile&MockObject */
        $entity = $this->getMockBuilder(FiasFileSelectorFile::class)->getMock();

        $entity->method('getPath')->willReturn($path);
        $entity->method('getSize')->willReturn($size);

        return $entity;
    }
}