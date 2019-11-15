<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use InvalidArgumentException;
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
     * Проверяет, что объект передаст вызов insert всем вложенным хранилищам.
     */
    public function testInsert()
    {
        $object = new stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('insert')->with($this->identicalTo($object));

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())->method('insert')->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->insert($object);
    }

    /**
     * Проверяет, что объект передаст вызов delete всем вложенным хранилищам.
     */
    public function testDelete()
    {
        $object = new stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('delete')->with($this->identicalTo($object));

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())->method('delete')->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->delete($object);
    }

    /**
     * Проверяет, что объект передаст вызов upsert всем вложенным хранилищам.
     */
    public function testUpsert()
    {
        $object = new stdClass();

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('upsert')->with($this->identicalTo($object));

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())->method('upsert')->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->upsert($object);
    }

    /**
     * Проверяет, что объект передаст вызов truncate всем вложенным хранилищам.
     */
    public function testTruncate()
    {
        $object = 'className';

        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('truncate')->with($this->identicalTo($object));

        $storage1 = $this->getMockBuilder(Storage::class)->getMock();
        $storage1->expects($this->once())->method('truncate')->with($this->identicalTo($object));

        $compositeStorage = new CompositeStorage([$storage, $storage1]);
        $compositeStorage->truncate($object);
    }
}
