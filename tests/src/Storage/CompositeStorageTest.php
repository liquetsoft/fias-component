<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Storage;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Exception\StorageException;
use Liquetsoft\Fias\Component\Storage\CompositeStorage;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use stdClass;

/**
 * Тест для объекта, который сохраняет данные в несколько хранилищ.
 */
class CompositeStorageTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если в массив конструктора передан объект,
     * который не реализует интерфейс хранилища.
     */
    public function testConstructWrongArgumentException()
    {
        $storage = $this->getMockBuilder(Storage::class)->getMock();

        $this->expectException(InvalidArgumentException::class);
        new CompositeStorage([$storage, 'test']);
    }

    /**
     * Проверяет, что объект передаст вызов start всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testStart()
    {
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())->method('start');

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->start();
    }

    /**
     * Проверяет, что объект передаст вызов stop всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testStop()
    {
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('stop');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())->method('stop');

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->stop();
    }

    /**
     * Проверяет, что объект проверит все вложенные хранилища поддерживают ли
     * они данный объект.
     */
    public function testSupports()
    {
        $object = new stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(false));

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(true));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $isSupport = $compositeStorage->supports($object);

        $this->assertTrue($isSupport);
    }

    /**
     * Проверяет, что объект проверит все вложенные хранилища поддерживают ли
     * они данный тип объектов.
     */
    public function testSupportsClass()
    {
        $class = $this->createFakeData()->word;

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supportsClass')
            ->with($this->identicalTo($class))
            ->will($this->returnValue(false));

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supportsClass')
            ->with($this->identicalTo($class))
            ->will($this->returnValue(true));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $isSupport = $compositeStorage->supportsClass($class);

        $this->assertTrue($isSupport);
    }

    /**
     * Проверяет, что объект передаст вызов insert всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testInsert()
    {
        $object = new stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(false));
        $storage->expects($this->never())->method('insert');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(true));
        $storage1->expects($this->once())->method('insert')->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->insert($object);
    }

    /**
     * Проверяет, что объект передаст вызов delete всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testDelete()
    {
        $object = new stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(false));
        $storage->expects($this->never())->method('delete');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(true));
        $storage1->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->delete($object);
    }

    /**
     * Проверяет, что объект передаст вызов upsert всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testUpsert()
    {
        $object = new stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(false));
        $storage->expects($this->never())->method('upsert');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supports')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(true));
        $storage1->expects($this->once())
            ->method('upsert')
            ->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->upsert($object);
    }

    /**
     * Проверяет, что объект передаст вызов truncate всем вложенным хранилищам.
     *
     * @throws StorageException
     */
    public function testTruncate()
    {
        $object = 'className';

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())
            ->method('supportsClass')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(false));
        $storage->expects($this->never())->method('truncate');

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())
            ->method('supportsClass')
            ->with($this->identicalTo($object))
            ->will($this->returnValue(true));
        $storage1->expects($this->once())->method('truncate')->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->truncate($object);
    }
}
