<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\DataInsertTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasEntityCase;
use Liquetsoft\Fias\Component\Tests\FiasFileSelectorCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use Liquetsoft\Fias\Component\Tests\SerializerCase;
use Liquetsoft\Fias\Component\Tests\StorageCase;
use Liquetsoft\Fias\Component\Tests\XmlReaderCase;

/**
 * Тест для зазачи, которая вставляет данные в хранилище.
 *
 * @internal
 */
class DataInsertTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FileSystemCase;
    use FiasEntityCase;
    use SerializerCase;
    use StorageCase;
    use FiasFileSelectorCase;
    use XmlReaderCase;

    /**
     * Проверяет, что задача обработает указанный файл.
     */
    public function testRun(): void
    {
        $fileName = '/test';
        $entityName = 'test_entity';
        $entityXmlPath = '/test/entity';
        $boundClass = 'test_class';
        $xmlLine1 = '<Test1 />';
        $objectForLine1 = new \stdClass();

        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::FILES_TO_PROCEED->value => [
                    $this->createFiasFileSelectorFileMock($fileName),
                ],
            ]
        );

        $fs = $this->createFileSystemMock();
        $fs->method('makeFileInfo')->willReturnCallback(
            fn (string $name): \SplFileInfo => match ($name) {
                $fileName => $this->createSplFileInfoMock($fileName),
                default => $this->createSplFileInfoMock('test_not_uses')
            }
        );

        $entity = $this->createFiasEntityMock($entityName, $entityXmlPath);
        $entity->expects($this->once())
            ->method('isFileNameFitsXmlInsertFileMask')
            ->with($this->identicalTo($fileName))
            ->willReturn(true);
        $entityRepo = $this->createFiasEntityRepoMock([$entity]);

        $entityBinder = $this->createFiasEntityBinderMock();
        $entityBinder->method('getImplementationByEntityName')->willReturnCallback(
            fn (FiasEntity $entity): ?string => match ($entity->getName()) {
                $entityName => $boundClass,
                default => null,
            }
        );

        $xmlReaderProvider = $this->createXmlReaderProviderMockIterator(
            $fileName,
            $entityXmlPath,
            [
                $xmlLine1,
            ]
        );

        $serializer = $this->createSerializerMockAwaitDeserialization(
            [
                [
                    'data' => $xmlLine1,
                    'type' => $boundClass,
                    'result' => $objectForLine1,
                ],
            ]
        );

        $storage = $this->createStorageMockSupports($objectForLine1);
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->expects($this->once())
            ->method('insert')
            ->with($this->identicalTo($objectForLine1));

        $task = new DataInsertTask(
            $entityRepo,
            $entityBinder,
            $xmlReaderProvider,
            $serializer,
            $storage,
            $fs,
        );

        $res = $task->run($state);

        $this->assertSame($state, $res);
    }
}
