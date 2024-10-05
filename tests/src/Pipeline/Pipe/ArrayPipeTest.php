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
        $state = $this->createDefaultStateMock([], true);
        $task1 = $this->createTaskMock($state);
        $task2 = $this->createTaskMock($state);

        $pipe = new ArrayPipe(
            [
                $task1,
                $task2,
            ]
        );

        $pipe->run($state);
    }

    /**
     * Проверяет, что состояние будет использовать предустановленный id.
     */
    public function testRunWithPreDefinedId(): void
    {
        $id = 'test_id';
        $state = $this->createDefaultStateMock(
            [
                StateParameter::PIPELINE_ID->value => $id,
            ]
        );

        $pipe = new ArrayPipe([$this->createTaskMock($state)]);
        $pipe->run($state);
        $res = $state->getParameterString(StateParameter::PIPELINE_ID);

        $this->assertSame($id, $res);
    }

    /**
     * Проверяет, что задачи добавляются в очередь и запускаются, а после задач запускается задача очистки.
     */
    public function testRunWithCleanup(): void
    {
        $state = $this->createDefaultStateMock([], true);
        $cleanUp = $this->createTaskMock($state);
        $task1 = $this->createTaskMock($state);
        $task2 = $this->createTaskMock($state);

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
     * Проверяет, что задачи могут остановить выполнения цепочки
     * с помощью объекта состояния.
     */
    public function testRunWithCompleted(): void
    {
        $state = $this->mock(State::class);
        $stateCounter = 0;
        $state->method('isCompleted')->willReturnCallback(
            function () use (&$stateCounter) {
                ++$stateCounter;

                return $stateCounter > 1;
            }
        );

        $cleanUp = $this->createTaskMock($state);
        $task1 = $this->createTaskMock($state);
        $task2 = $this->createTaskMock($state);
        $task3 = $this->createTaskMock(false);

        $pipe = new ArrayPipe(
            [
                $task1,
                $task2,
                $task3,
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
        $state = $this->createDefaultStateMock([], false);
        $cleanUp = $this->createTaskMock($state);
        $task1 = $this->createTaskMock($state);
        $task2 = $this->createTaskMock($state, new \InvalidArgumentException());

        $logger = $this->mock(LoggerInterface::class);
        $logger->expects($this->exactly(8))->method('log');

        $pipe = new ArrayPipe(
            [
                $task1,
                $task2,
            ],
            $cleanUp,
            $logger
        );

        $this->expectException(PipeException::class);
        $pipe->run($state);
    }

    /**
     * Проверяет, что очередь пишет данные в лог.
     */
    public function testLogger(): void
    {
        $state = $this->createDefaultStateMock([], true);

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
            [
                $this->createTaskMock($state),
            ],
            null,
            $logger
        );

        $pipe->run($state);
    }

    /**
     * Проверяет, что очередь передаст объект лога в задачу, если требуется.
     */
    public function testLoggableTaskLoggerInjected(): void
    {
        $state = $this->createDefaultStateMock([], true);

        $logger = $this->mock(LoggerInterface::class);

        $task = $this->mock(ArrayPipeTestLoggableMock::class);
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
            [
                $task,
            ],
            null,
            $logger
        );

        $pipe->run($state);
    }

    /**
     * Создает мок для новой задачи.
     */
    private function createTaskMock(mixed $with = null, ?\Throwable $exception = null): Task
    {
        $task = $this->mock(Task::class);

        if ($with !== null || $exception !== null) {
            $expects = $with === false ? $this->never() : $this->once();
            $method = $task->expects($expects)->method('run');
            if ($with !== null) {
                $method->with($this->equalTo($with));
            }
            if ($exception !== null) {
                $method->will($this->throwException($exception));
            }
        }

        return $task;
    }
}
