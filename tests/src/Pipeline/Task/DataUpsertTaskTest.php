<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Parser\XmlParser;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\Task\DataUpsertTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Reader\XmlReader;
use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для задачи, которая обновляет данные данные из файла в БД.
 */
class DataUpsertTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект читает и записывает данные.
     */
    public function testRun()
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/ActualStatuses/ActualStatus'));

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')->will($this->returnCallback(function ($file) use ($descriptor) {
            return $file === 'data.xml' ? $descriptor : null;
        }));
        $entityManager->method('getClassByDescriptor')->will($this->returnCallback(function ($testDescriptor) use ($descriptor) {
            return $testDescriptor === $descriptor ? DataUpsertTaskObject::class : null;
        }));

        $insertedData = [];
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')->will($this->returnCallback(function ($object) use (&$insertedData) {
            return $object->getActstatid() === 321;
        }));
        $storage->method('upsert')->will($this->returnCallback(function ($object) use (&$insertedData) {
            $insertedData[] = $object->getActstatid();
        }));

        $file = new SplFileInfo(__DIR__ . '/_fixtures/data.xml');
        $state = new ArrayState;
        $state->setParameter(Task::FILES_TO_INSERT_PARAM, [$file->getPathname()]);

        $reader = new XmlReader;
        $reader->open($file, $descriptor);

        $task = new DataUpsertTask($entityManager, new XmlParser($reader, new FiasSerializer), $storage);
        $task->run($state);

        $this->assertSame([321], $insertedData);
    }
}

/**
 * Мок для проверки задачи об обновлении данных в БД.
 */
class DataUpsertTaskObject
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
