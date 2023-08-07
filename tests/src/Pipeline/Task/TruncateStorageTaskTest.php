<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Pipeline\Task\TruncateStorageTask;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasEntityCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для задачи, которая очищает все данные для сущностей в хранилище.
 *
 * @internal
 */
class TruncateStorageTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FiasEntityCase;

    /**
     * Проверяет, что объект удалит все данные.
     */
    public function testRun(): void
    {
        $bindings = [
            'test_entity' => 'test',
            'test1_entity' => 'test1',
            'test2_entity' => 'test2',
        ];
        $binder = $this->createFiasEntityBinderMock();
        $binder->method('getBindings')->willReturn($bindings);

        /** @var Storage&MockObject */
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supports')
            ->willReturnCallback(
                fn (string $class): bool => $class === 'test' || $class === 'test2'
            );
        $storage->expects($this->exactly(2))
            ->method('truncate')
            ->with(
                $this->callback(
                    fn (string $class): bool => $class === 'test' || $class === 'test2'
                )
            );

        $logger = $this->createLoggerMockExpectsMessages(
            [
                [
                    'message' => 'Strorage for entity truncated',
                    'context' => [
                        'entity' => 'test_entity',
                        'boundClass' => 'test',
                    ],
                ],
                [
                    'message' => 'Strorage for entity truncated',
                    'context' => [
                        'entity' => 'test2_entity',
                        'boundClass' => 'test2',
                    ],
                ],
            ]
        );

        $state = $this->createPipelineStateMock();

        $task = new TruncateStorageTask($binder, $storage);
        $task->injectLogger($logger);
        $newState = $task->run($state);

        $this->assertSame($state, $newState);
    }
}
