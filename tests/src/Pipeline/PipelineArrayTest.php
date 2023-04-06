<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline;

use Liquetsoft\Fias\Component\Exception\PipelineException;
use Liquetsoft\Fias\Component\Helper\IdHelper;
use Liquetsoft\Fias\Component\Pipeline\PipelineArray;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use Psr\Log\LogLevel;

/**
 * Тест для объекта, который содержит внутренний массив со списком операций для исполнения.
 *
 * @internal
 */
class PipelineArrayTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;

    /**
     * Проверяет, что объект запускает список задач на исполнение.
     */
    public function testRun(): void
    {
        $state = $this->createPipelineStateMock();
        $state1 = $this->createPipelineStateMock();
        $state2 = $this->createPipelineStateMock();

        $task1 = $this->createPipelineTaskMock();
        $task1->expects($this->once())
            ->method('run')
            ->with($this->identicalTo($state))
            ->willReturn($state1);

        $task2 = $this->createPipelineTaskMock();
        $task2->expects($this->once())
            ->method('run')
            ->with($this->identicalTo($state1))
            ->willReturn($state2);

        $pipeline = new PipelineArray([$task1, $task2]);
        $pipeline->run($state);
    }

    /**
     * Проверяет, что объект прервет исполнение, если задача установит флаг.
     */
    public function testRunWithInterraption(): void
    {
        $state = $this->createPipelineStateMock();
        $state1 = $this->createPipelineStateMock([PipelineStateParam::INTERRUPT_PIPELINE->value => true]);

        $task1 = $this->createPipelineTaskMock();
        $task1->expects($this->once())->method('run')
            ->with($this->identicalTo($state))
            ->willReturn($state1);

        $task2 = $this->createPipelineTaskMock();
        $task2->expects($this->never())->method('run');

        $logger = $this->createLoggerMock();
        $logger->expects($this->exactly(6))->method('log');

        $pipeline = new PipelineArray([$task1, $task2], null, $logger);
        $pipeline->run($state);
    }

    /**
     * Проверяет, что объект запускает задачу по очистке.
     */
    public function testRunWithCleanup(): void
    {
        $state = $this->createPipelineStateMock();

        $task1 = $this->createPipelineTaskMock();
        $task1->expects($this->once())
            ->method('run')
            ->with($this->identicalTo($state))
            ->willReturn($state);

        $cleanUp = $this->createPipelineTaskMock();
        $cleanUp->expects($this->once())
            ->method('run')
            ->with($this->identicalTo($state))
            ->willReturn($state);

        $pipeline = new PipelineArray([$task1], $cleanUp);
        $pipeline->run($state);
    }

    /**
     * Проверяет, что объект правильно обработает исключение от задачи.
     */
    public function testRunTaskException(): void
    {
        $message = 'exception message test';
        $state = $this->createPipelineStateMock();

        $task1 = $this->createPipelineTaskMock();
        $task1->expects($this->once())
            ->method('run')
            ->with($this->identicalTo($state))
            ->willThrowException(new \RuntimeException($message));

        $cleanUp = $this->createPipelineTaskMock();
        $cleanUp->expects($this->once())
            ->method('run')
            ->with($this->identicalTo($state))
            ->willReturn($state);

        $logger = $this->createLoggerMock();
        $logger->expects($this->exactly(6))->method('log');

        $pipeline = new PipelineArray([$task1], $cleanUp, $logger);

        $this->expectException(PipelineException::class);
        $this->expectExceptionMessage($message);
        $pipeline->run($state);
    }

    /**
     * Проверяет, что объект использует логгер.
     */
    public function testRunLogger(): void
    {
        $state = $this->createPipelineStateMock();

        $task1 = $this->createPipelineTaskMock();
        $task1->expects($this->once())->method('run')->with($this->identicalTo($state))->willReturn($state);

        $logger = $this->createLoggerMock();
        $logger->expects($this->exactly(5))
            ->method('log')
            ->with(
                $this->equalTo(LogLevel::INFO),
                $this->anything(),
                $this->callback(
                    fn (array $ctx): bool => isset($ctx['source'], $ctx['pipeline_class'], $ctx['pipeline_id'])
                        && $ctx['source'] === IdHelper::getComponentId()
                        && $ctx['pipeline_class'] === PipelineArray::class
                        && \strlen((string) $ctx['pipeline_id']) === 32
                )
            );

        $pipeline = new PipelineArray([$task1], null, $logger);
        $pipeline->run($state);
    }

    /**
     * Проверяет, что объект передаст логгер в задачу.
     */
    public function testRunWithLogAwareTask(): void
    {
        $state = $this->createPipelineStateMock();

        $logger = $this->createLoggerMock();

        $task1 = $this->createPipelineTaskLogAwareMock();
        $task1->expects($this->once())->method('run')->with($this->identicalTo($state))->willReturn($state);
        $task1->expects($this->once())
            ->method('injectLogger')
            ->with(
                $this->identicalTo($logger),
                $this->callback(
                    fn (array $ctx): bool => isset($ctx['source'], $ctx['pipeline_class'], $ctx['pipeline_id'], $ctx['task'])
                        && $ctx['task'] === \get_class($task1)
                )
            );

        $pipeline = new PipelineArray([$task1], null, $logger);
        $pipeline->run($state);
    }
}
