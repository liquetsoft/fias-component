<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\DataAbstractTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasEntityCase;
use Liquetsoft\Fias\Component\Tests\FiasFileSelectorCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use Liquetsoft\Fias\Component\Tests\SerializerCase;
use Liquetsoft\Fias\Component\Tests\StorageCase;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderIterator;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для абстрактного класса задач связанных с обработкой данных: создания, обновления, удаления.
 *
 * @internal
 */
class DataAbstractTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FileSystemCase;
    use FiasEntityCase;
    use SerializerCase;
    use StorageCase;
    use FiasFileSelectorCase;

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

        $entityRepo = $this->createFiasEntityRepoMock();

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

        $logger = $this->createLoggerMockExpectsMessages(
            [
                [
                    'message' => 'Processing data from file',
                    'context' => [
                        'file' => $fileName,
                        'entity' => $entityName,
                        'xmlPath' => $entityXmlPath,
                        'boundClass' => $boundClass,
                    ],
                ],
                [
                    'message' => 'Data processing is completed',
                    'context' => [
                        'total' => 1,
                        'file' => $fileName,
                        'entity' => $entityName,
                        'xmlPath' => $entityXmlPath,
                        'boundClass' => $boundClass,
                    ],
                ],
            ]
        );

        /** @var DataAbstractTask&MockObject */
        $task = $this->getMockForAbstractClass(
            DataAbstractTask::class,
            [
                $entityRepo,
                $entityBinder,
                $xmlReaderProvider,
                $serializer,
                $storage,
                $fs,
            ]
        );
        $task->method('getFiasEntityByFile')->willReturnCallback(
            fn (\SplFileInfo $f): ?FiasEntity => match ($f->getPathname()) {
                $fileName => $this->createFiasEntityMock($entityName, $entityXmlPath),
                default => null,
            }
        );
        $task->expects($this->once())
            ->method('processItem')
            ->with(
                $this->identicalTo($objectForLine1),
                $this->identicalTo($storage)
            );

        $task->injectLogger($logger);
        $res = $task->run($state);

        $this->assertSame($state, $res);
    }

    /**
     * Проверяет, что задача пропустит нераспакованные файлы.
     */
    public function testRunIgnoreArchive(): void
    {
        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::FILES_TO_PROCEED->value => [
                    $this->createFiasFileSelectorFileMock('/test', 123, '/archive'),
                ],
            ]
        );

        /** @var DataAbstractTask&MockObject */
        $task = $this->getMockForAbstractClass(
            DataAbstractTask::class,
            [
                $this->createFiasEntityRepoMock(),
                $this->createFiasEntityBinderMock(),
                $this->createXmlReaderProviderMock(),
                $this->createSerializerMock(),
                $this->createStorageMock(),
                $this->createFileSystemMock(),
            ]
        );
        $task->expects($this->never())->method('processItem');

        $res = $task->run($state);

        $this->assertSame($state, $res);
    }

    /**
     * Проверяет, что задача пропустит файлы, для которых не найдена соответствующая сущность.
     */
    public function testRunIgnoreFileUnrelatedToEntities(): void
    {
        $fileName = '/test';

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

        /** @var DataAbstractTask&MockObject */
        $task = $this->getMockForAbstractClass(
            DataAbstractTask::class,
            [
                $this->createFiasEntityRepoMock(),
                $this->createFiasEntityBinderMock(),
                $this->createXmlReaderProviderMock(),
                $this->createSerializerMock(),
                $this->createStorageMock(),
                $fs,
            ]
        );
        $task->expects($this->once())
            ->method('getFiasEntityByFile')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $fileName
                )
            )
            ->willReturn(null);
        $task->expects($this->never())->method('processItem');

        $res = $task->run($state);

        $this->assertSame($state, $res);
    }

    /**
     * Проверяет, что задача пропустит файлы, для которых не найден класс с реализацией.
     */
    public function testRunIgnoreUnboundEntities(): void
    {
        $fileName = '/test';
        $entityName = 'test_entity';

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

        $entityBinder = $this->createFiasEntityBinderMock();
        $entityBinder->method('getImplementationByEntityName')->willReturn(null);

        /** @var DataAbstractTask&MockObject */
        $task = $this->getMockForAbstractClass(
            DataAbstractTask::class,
            [
                $this->createFiasEntityRepoMock(),
                $entityBinder,
                $this->createXmlReaderProviderMock(),
                $this->createSerializerMock(),
                $this->createStorageMock(),
                $fs,
            ]
        );
        $task->expects($this->once())
            ->method('getFiasEntityByFile')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $fileName
                )
            )
            ->willReturn(
                $this->createFiasEntityMock($entityName)
            );
        $task->expects($this->never())->method('processItem');

        $res = $task->run($state);

        $this->assertSame($state, $res);
    }

    /**
     * Проверяет, что задача пропустит объекты, которые не поддерживаются хранилищем.
     */
    public function testRunIgnoreUnsupported(): void
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

        $entityBinder = $this->createFiasEntityBinderMock();
        $entityBinder->method('getImplementationByEntityName')->willReturn($boundClass);

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

        /** @var DataAbstractTask&MockObject */
        $task = $this->getMockForAbstractClass(
            DataAbstractTask::class,
            [
                $this->createFiasEntityRepoMock(),
                $entityBinder,
                $xmlReaderProvider,
                $serializer,
                $this->createStorageMockSupports(),
                $fs,
            ]
        );
        $task->expects($this->once())
            ->method('getFiasEntityByFile')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $fileName
                )
            )
            ->willReturn(
                $this->createFiasEntityMock($entityName, $entityXmlPath)
            );
        $task->expects($this->never())->method('processItem');

        $res = $task->run($state);

        $this->assertSame($state, $res);
    }

    /**
     * Проверяет, что задача пропустит объекты, которые не прошли фильтрацию.
     */
    public function testRunIgnoreByFilter(): void
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

        $entityBinder = $this->createFiasEntityBinderMock();
        $entityBinder->method('getImplementationByEntityName')->willReturn($boundClass);

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

        /** @var DataAbstractTask&MockObject */
        $filter = $this->getMockBuilder(Filter::class)->getMock();
        $filter->expects($this->once())
            ->method('test')
            ->with($this->identicalTo($objectForLine1))
            ->willReturn(false);

        /** @var DataAbstractTask&MockObject */
        $task = $this->getMockForAbstractClass(
            DataAbstractTask::class,
            [
                $this->createFiasEntityRepoMock(),
                $entityBinder,
                $xmlReaderProvider,
                $serializer,
                $storage,
                $fs,
                $filter,
            ]
        );
        $task->expects($this->once())
            ->method('getFiasEntityByFile')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $fileName
                )
            )
            ->willReturn(
                $this->createFiasEntityMock($entityName, $entityXmlPath)
            );
        $task->expects($this->never())->method('processItem');

        $res = $task->run($state);

        $this->assertSame($state, $res);
    }

    /**
     * Проверяет, что задача правильно обработает исключение при загрузке.
     */
    public function testRunProcessItemException(): void
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

        $entityBinder = $this->createFiasEntityBinderMock();
        $entityBinder->method('getImplementationByEntityName')->willReturn($boundClass);

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

        /** @var DataAbstractTask&MockObject */
        $task = $this->getMockForAbstractClass(
            DataAbstractTask::class,
            [
                $this->createFiasEntityRepoMock(),
                $entityBinder,
                $xmlReaderProvider,
                $serializer,
                $storage,
                $fs,
            ]
        );
        $task->expects($this->once())
            ->method('getFiasEntityByFile')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $fileName
                )
            )
            ->willReturn(
                $this->createFiasEntityMock($entityName, $entityXmlPath)
            );
        $task->expects($this->once())
            ->method('processItem')
            ->willThrowException(new \Exception('test'));

        $this->expectException(\Exception::class);
        $task->run($state);
    }

    /**
     * Создает мок для объекта, который открывает xml файлы, с массивом данных для итерации.
     *
     * @return XmlReaderProvider&MockObject
     */
    private function createXmlReaderProviderMockIterator(string $fileName, string $xPath, array $stringsToRead): XmlReaderProvider
    {
        $it = (new \ArrayObject($stringsToRead))->getIterator();
        $iterator = $this->getMockBuilder(XmlReaderIterator::class)->getMock();
        $iterator->method('rewind')->willReturnCallback(function () use ($it): void { $it->rewind(); });
        $iterator->method('next')->willReturnCallback(function () use ($it): void { $it->next(); });
        $iterator->method('current')->willReturnCallback(fn (): mixed => $it->current());
        $iterator->method('key')->willReturnCallback(fn (): mixed => $it->key());
        $iterator->method('valid')->willReturnCallback(fn (): bool => $it->valid());
        $iterator->expects($this->once())->method('close');

        $provider = $this->createXmlReaderProviderMock();
        $provider->expects($this->once())
            ->method('open')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $fileName
                ),
                $this->identicalTo($xPath)
            )
            ->willReturn($iterator);

        return $provider;
    }

    /**
     * Создает мок для объекта, который открывает xml файлы.
     *
     * @return XmlReaderProvider&MockObject
     */
    private function createXmlReaderProviderMock(): XmlReaderProvider
    {
        /** @var XmlReaderProvider&MockObject */
        $provider = $this->getMockBuilder(XmlReaderProvider::class)->getMock();

        return $provider;
    }
}
