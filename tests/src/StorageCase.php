<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Liquetsoft\Fias\Component\Storage\Storage;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Трэйт, который содержит методы для создания моков логгера.
 */
trait StorageCase
{
    /**
     * Создает мок для сериализатора.
     *
     * @param object[] $items
     *
     * @return Storage&MockObject
     */
    public function createStorageMockSupports(...$items): Storage
    {
        $storage = $this->createStorageMock();
        $storage->method('supports')->willReturnCallback(
            fn (object $i): bool => \in_array($i, $items, true)
        );

        return $storage;
    }

    /**
     * Создает мок для сериализатора.
     *
     * @return Storage&MockObject
     */
    public function createStorageMock(): Storage
    {
        return $this->getMockBuilder(Storage::class)->getMock();
    }
}
