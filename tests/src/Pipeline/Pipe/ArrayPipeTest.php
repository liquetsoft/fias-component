<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Pipe;

use Liquetsoft\Fias\Component\Exception\PipeException;
use Liquetsoft\Fias\Component\Pipeline\Pipe\ArrayPipe;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\Mock\ArrayPipeTestLoggableMock;
use Psr\Log\LoggerInterface;

/**
 * Тест для объекта, который запускает на исполнение задачи из внутреннего массива.
 *
 * @internal
 */
final class ArrayPipeTest extends BaseCase
{
    /**
     * Проверяет, что задачи добавляются в очередь и запускаются.
     */
    public function testRun(): void
    {
        $pipelineId = 'pipeline_id';
        $fiasVersionNumber = 123;
        $fiasNextVersionFullUrl = 'https://test.test/full';

        $state = $this->createStateMock(
            [
                StateParameter::PIPELINE_ID->value => $pipelineId,
            ]
        );

        $task1 = $this->mock(Task::class);
        $task1->expects($this->once())
            ->method('run')
            ->willReturnCallback(
                fn (State $state): State => $state->setParameter(
                    StateParameter::FIAS_VERSION_NUMBER,
                    $fiasVersionNumber
                )
            );

        $task2 = $this->mock(Task::class);
        $task2->expects($this->once())
            ->method('run')
            ->willReturnCallback(
                fn (State $state): State => $state->setParameter(
                    StateParameter::FIAS_NEXT_VERSION_FULL_URL,
                    $fiasNextVersionFullUrl
                )
            );

        $pipe = new ArrayPipe(
            [
                $task1,
                $task2,
            ]
        );
        $finalState = $pipe->run($state);

        $this->assertNotSame($state, $finalState);
        $this->assertSame(
            $pipelineId,
            $finalState->getParameter(StateParameter::PIPELINE_ID)
        );
        $this->assertSame(
            $fiasVersionNumber,
            $finalState->getParameter(StateParameter::FIAS_VERSION_NUMBER)
        );
        $this->assertSame(
            $fiasNextVersionFullUrl,
            $finalState->getParameter(StateParameter::FIAS_NEXT_VERSION_FULL_URL)
        );
    }

    /**
     * Проверяет, что состояние будет использовать предустановленный id.
     */
    public function testRunDefaultPipelineIdIsSet(): void
    {
        $state = $this->createStateMock();

        $task = $this->mock(Task::class);
        $task->expects($this->any())->method('run')->willReturnArgument(0);

        $pipe = new ArrayPipe(
            [
                $task,
            ]
        );
        $finalState = $pipe->run($state);
        $res = $finalState->getParameterString(StateParameter::PIPELINE_ID);

        $this->assertNotEmpty($res);
    }

    /**
     * Проверяет, что после задач запускается задача очистки.
     */
    public function testRunWithCleanUp(): void
    {
        $state = $this->createStateMock();

        $task = $this->mock(Task::class);
        $task->expects($this->once())->method('run')->willReturnArgument(0);

        $cleanUp = $this->mock(Task::class);
        $cleanUp->expects($this->once())->method('run')->willReturnArgument(0);

        $pipe = new ArrayPipe(
            [
                $task,
            ],
            $cleanUp
        );

        $pipe->run($state);
    }

    /**
     * Проверяет, что задачи могут остановить выполнения цепочки
     * с помощью объекта состояния.
     */
    public function testRunTaskSetIsCompleted(): void
    {
        $state = $this->createStateMock();

        $task1 = $this->mock(Task::class);
        $task1->expects($this->once())
            ->method('run')
            ->willReturnCallback(
                fn (State $state): State => $state->complete()
            );

        $task2 = $this->mock(Task::class);
        $task2->expects($this->never())->method('run');

        $pipe = new ArrayPipe(
            [
                $task1,
                $task2,
            ]
        );

        $pipe->run($state);
    }

    /**
     * Проверяет, что если исполнение было остановлено задачей, то все равно запустится clean up.
     */
    public function testRunTaskSetIsCompletedWithCleanUp(): void
    {
        $state = $this->createStateMock();

        $task1 = $this->mock(Task::class);
        $task1->expects($this->any())
            ->method('run')
            ->willReturnCallback(
                fn (State $state): State => $state->complete()
            );

        $task2 = $this->mock(Task::class);
        $task2->expects($this->any())->method('run')->willReturnArgument(0);

        $cleanUp = $this->mock(Task::class);
        $cleanUp->expects($this->once())->method('run')->willReturnArgument(0);

        $pipe = new ArrayPipe(
            [
                $task1,
                $task2,
            ],
            $cleanUp
        );

        $pipe->run($state);
    }

    /**
     * Проверяет, что объект приложения перехватит любое исключение и выбросит
     * унифицированный тип.
     */
    public function testRunException(): void
    {
        $error = 'test error';

        $state = $this->createStateMock();

        $task = $this->mock(Task::class);
        $task->expects($this->once())->method('run')->willThrowException(
            new \InvalidArgumentException($error)
        );

        $pipe = new ArrayPipe(
            [
                $task,
            ],
        );

        $this->expectException(PipeException::class);
        $this->expectExceptionMessage($error);
        $pipe->run($state);
    }

    /**
     * Проверяет, что clean up запустится, нсли было выброшено исключение.
     */
    public function testRunExceptionWithCleanUp(): void
    {
        $state = $this->createStateMock();

        $task = $this->mock(Task::class);
        $task->expects($this->once())->method('run')->willThrowException(
            new \InvalidArgumentException()
        );

        $cleanUp = $this->mock(Task::class);
        $cleanUp->expects($this->once())->method('run')->willReturnArgument(0);

        $pipe = new ArrayPipe(
            [
                $task,
            ],
            $cleanUp
        );

        $this->expectException(PipeException::class);
        $pipe->run($state);
    }

    /**
     * Проверяет, что исключение будет залогировано.
     */
    public function testRunExceptionWithLogger(): void
    {
        $state = $this->createStateMock();

        $task = $this->mock(Task::class);
        $task->expects($this->once())->method('run')->willThrowException(
            new \InvalidArgumentException()
        );

        $logger = $this->mock(LoggerInterface::class);
        $logger->expects($this->exactly(4))
            ->method('log')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->logicalAnd(
                    $this->arrayHasKey('pipeline_class'),
                    $this->arrayHasKey('pipeline_id')
                )
            );

        $pipe = new ArrayPipe(
            [
                $task,
            ],
            logger: $logger
        );

        $this->expectException(PipeException::class);
        $pipe->run($state);
    }

    /**
     * Проверяет, что очередь пишет данные в лог.
     */
    public function testRunWithLogger(): void
    {
        $state = $this->createStateMock();

        $task = $this->mock(Task::class);
        $task->expects($this->once())->method('run')->willReturnArgument(0);

        $logger = $this->mock(LoggerInterface::class);
        $logger->expects($this->exactly(5))
            ->method('log')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->logicalAnd(
                    $this->arrayHasKey('pipeline_class'),
                    $this->arrayHasKey('pipeline_id')
                )
            );

        $pipe = new ArrayPipe(
            tasks: [
                $task,
            ],
            logger: $logger
        );

        $pipe->run($state);
    }

    /**
     * Проверяет, что очередь передаст объект лога в задачу, если требуется.
     */
    public function testLoggableTaskLoggerInjected(): void
    {
        $state = $this->createStateMock();

        $logger = $this->mock(LoggerInterface::class);

        $task = $this->mock(ArrayPipeTestLoggableMock::class);
        $task->expects($this->any())->method('run')->willReturnArgument(0);
        $task->expects($this->once())
            ->method('injectLogger')
            ->with(
                $this->identicalTo($logger),
                $this->logicalAnd(
                    $this->arrayHasKey('pipeline_class'),
                    $this->arrayHasKey('pipeline_id'),
                    $this->arrayHasKey('task')
                )
            );

        $pipe = new ArrayPipe(
            tasks: [
                $task,
            ],
            logger: $logger
        );

        $pipe->run($state);
    }
}
