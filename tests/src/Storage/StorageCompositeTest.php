<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Storage;

use Liquetsoft\Fias\Component\Exception\StorageException;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Storage\StorageComposite;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который сохраняет данные в несколько хранилищ.
 *
 * @internal
 */
class StorageCompositeTest extends BaseCase
{
    /**
     * Проверяет, что объект передаст вызов start всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testStart(): void
    {
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())->method('start');

        $StorageComposite = new StorageComposite([$storage, $storage1]);
        $StorageComposite->start();
    }

    /**
     * Проверяет, что объект передаст вызов stop всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testStop(): void
    {
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('stop');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())->method('stop');

        $StorageComposite = new StorageComposite([$storage, $storage1]);
        $StorageComposite->stop();
    }

    /**
     * Проверяет, что объект проверит все вложенные хранилища поддерживают ли
     * они данный объект.
     */
    public function testSupports(): void
    {
        $object = new \stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(false);

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(true);

        $storage2 = $this->getMockBuilder(Storage::class)->getMock();
        $storage2->expects($this->never())->method('supports');

        $StorageComposite = new StorageComposite([$storage, $storage1, $storage2]);
        $isSupport = $StorageComposite->supports($object);

        $this->assertTrue($isSupport);
    }

    /**
     * Проверяет, что объект проверит все вложенные хранилища поддерживают ли
     * они данный тип объектов.
     */
    public function testSupportsClass(): void
    {
        $class = self::class;

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($class))
            ->willReturn(false);

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($class))
            ->willReturn(true);

        $StorageComposite = new StorageComposite([$storage, $storage1]);
        $isSupport = $StorageComposite->supports($class);

        $this->assertTrue($isSupport);
    }

    /**
     * Проверяет, что объект вернет false, если вложенные хранилища не заданы.
     */
    public function testSupportsEmptyStoragesList(): void
    {
        $object = new \stdClass();

        $StorageComposite = new StorageComposite([]);
        $isSupport = $StorageComposite->supports($object);

        $this->assertFalse($isSupport);
    }

    /**
     * Проверяет, что объект передаст вызов insert всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testInsert(): void
    {
        $object = new \stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(false);
        $storage->expects($this->never())->method('insert');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(true);
        $storage1->expects($this->once())->method('insert')->with($this->identicalTo($object));

        $StorageComposite = new StorageComposite([$storage, $storage1]);
        $StorageComposite->insert($object);
    }

    /**
     * Проверяет, что объект передаст вызов delete всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testDelete(): void
    {
        $object = new \stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(false);
        $storage->expects($this->never())->method('delete');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(true);
        $storage1->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($object));

        $StorageComposite = new StorageComposite([$storage, $storage1]);
        $StorageComposite->delete($object);
    }

    /**
     * Проверяет, что объект передаст вызов upsert всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testUpsert(): void
    {
        $object = new \stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(false);
        $storage->expects($this->never())->method('upsert');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->willReturn(true);
        $storage1->expects($this->once())
            ->method('upsert')
            ->with($this->identicalTo($object));

        $StorageComposite = new StorageComposite([$storage, $storage1]);
        $StorageComposite->upsert($object);
    }

    /**
     * Проверяет, что объект передаст вызов truncate всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testTruncate(): void
    {
        $className = self::class;

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($className))
            ->willReturn(false);
        $storage->expects($this->never())->method('truncate');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($className))
            ->willReturn(true);
        $storage1->expects($this->once())->method('truncate')->with($this->identicalTo($className));

        $StorageComposite = new StorageComposite([$storage, $storage1]);
        $StorageComposite->truncate($className);
    }
}
