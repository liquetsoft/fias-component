<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Pipeline\Task\DataUpsertTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\Mock\DataUpsertTaskMock;
use Liquetsoft\Fias\Component\XmlReader\BaseXmlReader;

/**
 * Тест для задачи, которая обновляет данные данные из файла в БД.
 *
 * @internal
 */
class DataUpsertTaskTest extends BaseCase
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
                    return $testDescriptor === $descriptor ? DataUpsertTaskMock::class : null;
                }
            );

        $insertedData = [];
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')
            ->willReturnCallback(
                function (DataUpsertTaskMock $object) use (&$insertedData) {
                    return $object->getActstatid() === 321;
                }
            );
        $storage->method('upsert')
            ->willReturnCallback(
                function (DataUpsertTaskMock $object) use (&$insertedData): void {
                    $insertedData[] = $object->getActstatid();
                }
            );

        $state = $this->createDefaultStateMock(
            [
                Task::FILES_TO_PROCEED => [__DIR__ . '/_fixtures/data.xml'],
            ]
        );

        $task = new DataUpsertTask(
            $entityManager,
            new BaseXmlReader(),
            $storage,
            new FiasSerializer()
        );
        $task->run($state);

        $this->assertSame([321], $insertedData);
    }
}
