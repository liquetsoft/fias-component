<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Storage;

use Liquetsoft\Fias\Component\Storage\CompositeStorage;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который сохраняет данные в несколько хранилищ.
 *
 * @internal
 */
final class CompositeStorageTest extends BaseCase
{
    /**
     * Проверяет, что объект передаст вызов start всем вложенным хранилищам.
     */
    public function testStart(): void
    {
        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())->method('start');

        $storage1 = $this->mock(Storage::class);
        $storage1->expects($this->once())->method('start');

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->start();
    }

    /**
     * Проверяет, что объект передаст вызов stop всем вложенным хранилищам.
     */
    public function testStop(): void
    {
        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())->method('stop');

        $storage1 = $this->mock(Storage::class);
        $storage1->expects($this->once())->method('stop');

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->stop();
    }

    /**
     * Проверяет, что объект проверит все вложенные хранилища поддерживают ли
     * они данный объект.
     */
    public function testSupports(): void
    {
        $object = new \stdClass();

        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(false);

        $storage1 = $this->mock(Storage::class);
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(true);

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $isSupport = $compositeStorage->supports($object);

        $this->assertTrue($isSupport);
    }

    /**
     * Проверяет, что объект проверит все вложенные хранилища поддерживают ли
     * они данный тип объектов.
     */
    public function testSupportsClass(): void
    {
        $class = $this->createFakeData()->word();

        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())
            ->method('supportsClass')
            ->with($this->identicalTo($class))
            ->willReturn(false);

        $storage1 = $this->mock(Storage::class);
        $storage1->expects($this->once())
            ->method('supportsClass')
            ->with($this->identicalTo($class))
            ->willReturn(true);

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $isSupport = $compositeStorage->supportsClass($class);

        $this->assertTrue($isSupport);
    }

    /**
     * Проверяет, что объект передаст вызов insert всем вложенным хранилищам.
     */
    public function testInsert(): void
    {
        $object = new \stdClass();

        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(false);
        $storage->expects($this->never())->method('insert');

        $storage1 = $this->mock(Storage::class);
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(true);
        $storage1->expects($this->once())->method('insert')->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->insert($object);
    }

    /**
     * Проверяет, что объект передаст вызов delete всем вложенным хранилищам.
     */
    public function testDelete(): void
    {
        $object = new \stdClass();

        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(false);
        $storage->expects($this->never())->method('delete');

        $storage1 = $this->mock(Storage::class);
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(true);
        $storage1->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->delete($object);
    }

    /**
     * Проверяет, что объект передаст вызов upsert всем вложенным хранилищам.
     */
    public function testUpsert(): void
    {
        $object = new \stdClass();

        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(false);
        $storage->expects($this->never())->method('upsert');

        $storage1 = $this->mock(Storage::class);
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(true);
        $storage1->expects($this->once())
            ->method('upsert')
            ->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->upsert($object);
    }

    /**
     * Проверяет, что объект передаст вызов truncate всем вложенным хранилищам.
     */
    public function testTruncate(): void
    {
        $object = 'className';

        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())
            ->method('supportsClass')
            ->with($this->identicalTo($object))
            ->willReturn(false);
        $storage->expects($this->never())->method('truncate');

        $storage1 = $this->mock(Storage::class);
        $storage1->expects($this->once())
            ->method('supportsClass')
            ->with($this->identicalTo($object))
            ->willReturn(true);
        $storage1->expects($this->once())->method('truncate')->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->truncate($object);
    }
}
