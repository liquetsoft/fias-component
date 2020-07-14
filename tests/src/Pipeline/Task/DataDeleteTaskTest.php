<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\Task\DataDeleteTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Reader\XmlReader;

/**
 * Тест для задачи, которая удаляет данные из файла из БД.
 */
class DataDeleteTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект читает и записывает данные.
     */
    public function testRun()
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/ActualStatuses/ActualStatus'));

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByDeleteFile')->will($this->returnCallback(function ($file) use ($descriptor) {
            return $file === 'data.xml' ? $descriptor : null;
        }));
        $entityManager->method('getClassByDescriptor')->will($this->returnCallback(function ($testDescriptor) use ($descriptor) {
            return $testDescriptor === $descriptor ? DataInsertTaskObject::class : null;
        }));

        $insertedData = [];
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')->will($this->returnCallback(function ($object) use (&$insertedData) {
            return $object->getActstatid() === 321;
        }));
        $storage->method('delete')->will($this->returnCallback(function ($object) use (&$insertedData) {
            $insertedData[] = $object->getActstatid();
        }));

        $state = new ArrayState;
        $state->setParameter(Task::FILES_TO_DELETE_PARAM, [__DIR__ . '/_fixtures/data.xml']);

        $task = new DataDeleteTask($entityManager, new XmlReader, $storage, new FiasSerializer);
        $task->run($state);

        $this->assertSame([321], $insertedData);
    }
}

/**
 * Мок для проверки задачи об удалении данных из БД.
 */
class DataDeleteTaskObject
{
    private $actstatid;
    private $name;

    public function setActstatid(int $actstatid)
    {
        $this->actstatid = $actstatid;
    }

    public function getActstatid()
    {
        return $this->actstatid;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
