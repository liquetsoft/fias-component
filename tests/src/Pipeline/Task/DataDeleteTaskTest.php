<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\DataDeleteTask;
use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\Mock\DataDeleteTaskMock;
use Liquetsoft\Fias\Component\XmlReader\BaseXmlReader;

/**
 * Тест для задачи, которая удаляет данные из файла из БД.
 *
 * @internal
 */
final class DataDeleteTaskTest extends BaseCase
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
            ->method('getDescriptorByDeleteFile')
            ->willReturnCallback(
                function (string $file) use ($descriptor) {
                    return $file === 'data.xml' ? $descriptor : null;
                }
            );
        $entityManager->expects($this->any())
            ->method('getClassByDescriptor')
            ->willReturnCallback(
                function (EntityDescriptor $testDescriptor) use ($descriptor) {
                    return $testDescriptor === $descriptor ? DataDeleteTaskMock::class : null;
                }
            );

        $insertedData = [];
        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')
            ->willReturnCallback(
                function (DataDeleteTaskMock $object) {
                    return $object->getActstatid() === 321;
                }
            );
        $storage->method('delete')
            ->willReturnCallback(
                function (DataDeleteTaskMock $object) use (&$insertedData): void {
                    $insertedData[] = $object->getActstatid();
                }
            );

        $state = $this->createDefaultStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => [__DIR__ . '/_fixtures/data.xml'],
            ]
        );

        $task = new DataDeleteTask(
            $entityManager,
            new BaseXmlReader(),
            $storage,
            new FiasSerializer()
        );
        $task->run($state);

        $this->assertSame([321], $insertedData);
    }
}
