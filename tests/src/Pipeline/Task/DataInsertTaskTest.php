<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\DataInsertTask;
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
final class DataInsertTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект читает и записывает данные.
     */
    public function testRun(): void
    {
        $descriptor = $this->mock(EntityDescriptor::class);
        $descriptor->expects($this->any())
            ->method('getXmlPath')
            ->willReturn('/ActualStatuses/ActualStatus');

        $entityManager = $this->mock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getDescriptorByInsertFile')
            ->willReturnCallback(
                function (string $file) use ($descriptor) {
                    return $file === 'data.xml' ? $descriptor : null;
                }
            );
        $entityManager->expects($this->any())
            ->method('getClassByDescriptor')
            ->willReturnCallback(
                function (EntityDescriptor $testDescriptor) use ($descriptor) {
                    return $testDescriptor === $descriptor ? DataInsertTaskMock::class : null;
                }
            );

        $insertedData = [];
        $storage = $this->mock(Storage::class);
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

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => [__DIR__ . '/_fixtures/data.xml'],
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
     */
    public function testRunDeserializeException(): void
    {
        $descriptor = $this->mock(EntityDescriptor::class);
        $descriptor->expects($this->any())
            ->method('getXmlPath')
            ->willReturn('/ActualStatuses/ActualStatus');

        $entityManager = $this->mock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getDescriptorByInsertFile')
            ->willReturnCallback(
                function (string $file) use ($descriptor) {
                    return $file === 'data.xml' ? $descriptor : null;
                }
            );
        $entityManager->expects($this->any())
            ->method('getClassByDescriptor')
            ->willReturnCallback(
                function (EntityDescriptor $testDescriptor) use ($descriptor) {
                    return $testDescriptor === $descriptor ? DataInsertTaskMock::class : null;
                }
            );

        $insertedData = [];
        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')->willReturn(true);
        $storage->method('insert')
            ->willReturnCallback(
                function (DataInsertTaskMock $object) use (&$insertedData): void {
                    $insertedData[] = $object->getActstatid();
                }
            );

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => [__DIR__ . '/_fixtures/data.xml'],
            ]
        );

        $serializer = $this->mock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method('deserialize')
            ->will(
                $this->throwException(new \InvalidArgumentException())
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
     */
    public function testRunDeserializeNonObjectException(): void
    {
        $descriptor = $this->mock(EntityDescriptor::class);
        $descriptor->expects($this->any())
            ->method('getXmlPath')
            ->willReturn('/ActualStatuses/ActualStatus');

        $entityManager = $this->mock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getDescriptorByInsertFile')
            ->willReturnCallback(
                function (string $file) use ($descriptor) {
                    return $file === 'data.xml' ? $descriptor : null;
                }
            );
        $entityManager->expects($this->any())
            ->method('getClassByDescriptor')
            ->willReturnCallback(
                function (EntityDescriptor $testDescriptor) use ($descriptor) {
                    return $testDescriptor === $descriptor ? DataInsertTaskMock::class : null;
                }
            );

        $insertedData = [];
        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')->willReturn(true);
        $storage->method('insert')
            ->willReturnCallback(
                function (DataInsertTaskMock $object) use (&$insertedData): void {
                    $insertedData[] = $object->getActstatid();
                }
            );

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => [__DIR__ . '/_fixtures/data.xml'],
            ]
        );

        $serializer = $this->mock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method('deserialize')
            ->willReturn('test');

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
