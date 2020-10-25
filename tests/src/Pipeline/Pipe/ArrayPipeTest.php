<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Pipe;

use Exception;
use InvalidArgumentException;
use Liquetsoft\Fias\Component\Exception\PipeException;
use Liquetsoft\Fias\Component\Pipeline\Pipe\ArrayPipe;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\Mock\ArrayPipeTestLoggableMock;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * Тест для объекта, который запускает на исполнение задачи из внутреннего массива.
 */
class ArrayPipeTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение при попытке передать неверный параметр.
     *
     * @throws Exception
     */
    public function testConstructNoTaskInstanceException()
    {
        $task1 = $this->createTaskMock();
        $task2 = 'test';

        $this->expectException(InvalidArgumentException::class);
        new ArrayPipe(
            [
                $task1,
                $task2,
            ]
        );
    }

    /**
     * Проверяет, что задачи добавляются в очередь и запускаются.
     *
     * @throws Exception
     */
    public function testRun()
    {
        $state = $this->createStateMock();
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
     * Проверяет, что задачи добавляются в очередь и запускаются, а после задач запускается задача очистки.
     *
     * @throws Exception
     */
    public function testRunWithCleanup()
    {
        $state = $this->createStateMock();
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
     *
     * @throws PipeException
     * @throws Exception
     */
    public function testRunWithCompleted()
    {
        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->at(0))->method('isCompleted')->will($this->returnValue(false));
        $state->expects($this->at(1))->method('isCompleted')->will($this->returnValue(true));

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
     *
     * @throws Exception
     */
    public function testRunException()
    {
        $state = $this->createStateMock(false);
        $cleanUp = $this->createTaskMock($state);
        $task1 = $this->createTaskMock($state);
        $task2 = $this->createTaskMock($state, new InvalidArgumentException());

        $pipe = new ArrayPipe(
            [
                $task1,
                $task2,
            ],
            $cleanUp
        );

        $this->expectException(PipeException::class);
        $pipe->run($state);
    }

    /**
     * Проверяет, что очередь пишет данные в лог.
     *
     * @throws Exception
     */
    public function testLogger()
    {
        $state = $this->createStateMock();
        $task = $this->createTaskMock($state);

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->atLeastOnce())
            ->method('log')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->logicalAnd(
                    $this->arrayHasKey('pipeline_class'),
                    $this->arrayHasKey('pipeline_id')
                )
            )
        ;

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
     * Проверяет, что очередь передаст объект лога в задачу, если требуется.
     *
     * @throws Exception
     */
    public function testLoggableTaskLoggerInjected()
    {
        $state = $this->createStateMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $task = $this->getMockBuilder(ArrayPipeTestLoggableMock::class)->getMock();
        $task->expects($this->once())
            ->method('injectLogger')
            ->with(
                $this->identicalTo($logger),
                $this->logicalAnd(
                    $this->arrayHasKey('pipeline_class'),
                    $this->arrayHasKey('pipeline_id'),
                    $this->arrayHasKey('task')
                )
            )
        ;

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
     * Создает мок для объекта состояния.
     *
     * @param bool $isCompleted
     *
     * @return State
     */
    private function createStateMock(bool $isCompleted = true): State
    {
        $state = $this->getMockBuilder(State::class)->getMock();

        $expects = $isCompleted ? $this->once() : $this->never();
        $state->expects($expects)->method('complete');

        if (!($state instanceof State)) {
            throw new RuntimeException('Wrong state mock.');
        }

        return $state;
    }

    /**
     * Создает мок для новой задачи.
     *
     * @param mixed          $with
     * @param Throwable|null $exception
     *
     * @return Task
     */
    private function createTaskMock($with = null, ?Throwable $exception = null): Task
    {
        $task = $this->getMockBuilder(Task::class)->getMock();

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

        if (!($task instanceof Task)) {
            throw new RuntimeException('Wrong task mock.');
        }

        return $task;
    }
}
