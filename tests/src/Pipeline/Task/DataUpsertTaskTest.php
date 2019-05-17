<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\State;

use Liquetsoft\Fias\Component\Pipeline\Task\DataUpsertTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\XmlReader\XmlReader;
use Liquetsoft\Fias\Component\XmlReader\BaseXmlReader;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Symfony\Component\Serializer\Serializer;
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
        $descriptor->method('getXmlPath')->will($this->returnValue('/ActualStatuses/ActualStatus'));

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')->will($this->returnCallback(function ($file) use ($descriptor) {
            return $file === 'data.xml' ? $descriptor : null;
        }));
        $entityManager->method('getClassByDescriptor')->will($this->returnCallback(function ($testDescriptor) use ($descriptor) {
            return $testDescriptor === $descriptor ? DataInsertTaskObject::class : null;
        }));

        $insertedData = [];
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('upsert')->will($this->returnCallback(function ($object) use (&$insertedData) {
            $insertedData[] = $object->getActstatid();
        }));

        $state = new ArrayState;
        $state->setParameter(Task::EXTRACT_TO_FOLDER_PARAM, new SplFileInfo(__DIR__ . '/_fixtures'));

        $task = new DataUpsertTask($entityManager, new BaseXmlReader, $storage, new FiasSerializer);
        $task->run($state);

        $this->assertSame([123, 321], $insertedData);
    }

    /**
     * Проверяет, что объект выбросит исключение, если не указан каталог для чтения.
     */
    public function testRunEmptyUnpackToException()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $reader = $this->getMockBuilder(XmlReader::class)->getMock();
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $serializer = $this->getMockBuilder(Serializer::class)->getMock();
        $state = $this->getMockBuilder(State::class)->getMock();

        $task = new DataUpsertTask($entityManager, $reader, $storage, $serializer);

        $this->expectException(TaskException::class);
        $task->run($state);
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
