<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\Task\DataInsertTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\Mock\DataInsertTaskMock;
use Liquetsoft\Fias\Component\XmlReader\BaseXmlReader;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Тест для задачи, которая загружает данные из файла в БД.
 *
 * @internal
 */
class DataInsertTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект читает и записывает данные.
     *
     * @throws Exception
     */
    public function testRun(): void
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getXmlPath')->willReturn('/ActualStatuses/ActualStatus');

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')
            ->willReturnCallback(
                function (string $file) use ($descriptor) {
                    return $file === 'data.xml' ? $descriptor : null;
                }
            );
        $entityManager->method('getClassByDescriptor')
            ->willReturnCallback(
                function (EntityDescriptor $testDescriptor) use ($descriptor) {
                    return $testDescriptor === $descriptor ? DataInsertTaskMock::class : null;
                }
            );

        $insertedData = [];
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')
            ->willReturnCallback(
                function (DataInsertTaskMock $object) use (&$insertedData) {
                    return $object->getActstatid() === 321;
                }
            );
        $storage->method('insert')
            ->willReturnCallback(
                function (DataInsertTaskMock $object) use (&$insertedData): void {
                    $insertedData[] = $object->getActstatid();
                }
            );

        $state = $this->createDefaultStateMock(
            [
                Task::FILES_TO_PROCEED => [__DIR__ . '/_fixtures/data.xml'],
            ]
        );

        $task = new DataInsertTask(
            $entityManager,
            new BaseXmlReader(),
            $storage,
            new FiasSerializer()
        );
        $task->run($state);

        $this->assertSame([321], $insertedData);
    }

    /**
     * Проверяет, что объект обработает исключение от объекта, который преобразует строку.
     *
     * @throws Exception
     */
    public function testRunDeserializeException(): void
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getXmlPath')->willReturn('/ActualStatuses/ActualStatus');

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')
            ->willReturnCallback(
                function (string $file) use ($descriptor) {
                    return $file === 'data.xml' ? $descriptor : null;
                }
            );
        $entityManager->method('getClassByDescriptor')
            ->willReturnCallback(
                function (EntityDescriptor $testDescriptor) use ($descriptor) {
                    return $testDescriptor === $descriptor ? DataInsertTaskMock::class : null;
                }
            );

        $insertedData = [];
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')->willReturn(true);
        $storage->method('insert')
            ->willReturnCallback(
                function (DataInsertTaskMock $object) use (&$insertedData): void {
                    $insertedData[] = $object->getActstatid();
                }
            );

        $state = $this->createDefaultStateMock(
            [
                Task::FILES_TO_PROCEED => [__DIR__ . '/_fixtures/data.xml'],
            ]
        );

        $serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();
        $serializer->method('deserialize')
            ->will(
                $this->throwException(new InvalidArgumentException())
            );

        $task = new DataInsertTask(
            $entityManager,
            new BaseXmlReader(),
            $storage,
            $serializer
        );

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если десериализатор вернет не объект.
     *
     * @throws Exception
     */
    public function testRunDeserializeNonObjectException(): void
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getXmlPath')->willReturn('/ActualStatuses/ActualStatus');

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')
            ->willReturnCallback(
                function (string $file) use ($descriptor) {
                    return $file === 'data.xml' ? $descriptor : null;
                }
            );
        $entityManager->method('getClassByDescriptor')
            ->willReturnCallback(
                function (EntityDescriptor $testDescriptor) use ($descriptor) {
                    return $testDescriptor === $descriptor ? DataInsertTaskMock::class : null;
                }
            );

        $insertedData = [];
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')->willReturn(true);
        $storage->method('insert')
            ->willReturnCallback(
                function (DataInsertTaskMock $object) use (&$insertedData): void {
                    $insertedData[] = $object->getActstatid();
                }
            );

        $state = $this->createDefaultStateMock(
            [
                Task::FILES_TO_PROCEED => [__DIR__ . '/_fixtures/data.xml'],
            ]
        );

        $serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();
        $serializer->method('deserialize')->willReturn('test');

        $task = new DataInsertTask(
            $entityManager,
            new BaseXmlReader(),
            $storage,
            $serializer
        );

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
