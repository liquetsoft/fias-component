<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Pipe;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Exception\PipeException;
use Liquetsoft\Fias\Component\Pipeline\Pipe\ArrayPipe;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\LoggableTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Psr\Log\LoggerInterface;

/**
 * Тест для объекта, который запускает на исполнение задачи из внутреннего массива.
 */
class ArrayPipeTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение при попытке передть неверный параметр.
     */
    public function testConstructNoTaskInstanceException()
    {
        $cleanUp = $this->getMockBuilder(Task::class)->getMock();
        $task1 = $this->getMockBuilder(Task::class)->getMock();
        $task2 = 'test';

        $this->expectException(InvalidArgumentException::class);
        $pipe = new ArrayPipe([$task1, $task2], $cleanUp);
    }

    /**
     * Проверяет, что задачи добавляются в очередь и запускаются.
     */
    public function testRun()
    {
        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->once())->method('complete');

        $task1 = $this->getMockBuilder(Task::class)->getMock();
        $task1->expects($this->once())->method('run')->with($this->equalTo($state));

        $task2 = $this->getMockBuilder(Task::class)->getMock();
        $task2->expects($this->once())->method('run')->with($this->equalTo($state));

        $pipe = new ArrayPipe([$task1, $task2]);
        $pipe->run($state);
    }

    /**
     * Проверяет, что задачи добавляются в очередь и запускаются, а после задач запускается задача очистки.
     */
    public function testRunWithCleanup()
    {
        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->once())->method('complete');

        $cleanUp = $this->getMockBuilder(Task::class)->getMock();
        $cleanUp->expects($this->once())->method('run')->with($this->equalTo($state));

        $task1 = $this->getMockBuilder(Task::class)->getMock();
        $task1->expects($this->once())->method('run')->with($this->equalTo($state));

        $task2 = $this->getMockBuilder(Task::class)->getMock();
        $task2->expects($this->once())->method('run')->with($this->equalTo($state));

        $pipe = new ArrayPipe([$task1, $task2], $cleanUp);
        $pipe->run($state);
    }

    /**
     * Проверяет, что задачи могут остановить выполнения цепочки
     * с помощью объекта состояния.
     */
    public function testRunWithCompleted()
    {
        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->at(0))->method('isCompleted')->will($this->returnValue(false));
        $state->expects($this->at(1))->method('isCompleted')->will($this->returnValue(true));

        $cleanUp = $this->getMockBuilder(Task::class)->getMock();
        $cleanUp->expects($this->once())->method('run')->with($this->equalTo($state));

        $task1 = $this->getMockBuilder(Task::class)->getMock();
        $task1->expects($this->once())->method('run')->with($this->equalTo($state));

        $task2 = $this->getMockBuilder(Task::class)->getMock();
        $task2->expects($this->once())->method('run')->with($this->equalTo($state));

        $task3 = $this->getMockBuilder(Task::class)->getMock();
        $task3->expects($this->never())->method('run');

        $pipe = new ArrayPipe([$task1, $task2, $task3], $cleanUp);
        $pipe->run($state);
    }

    /**
     * Проверяет, что объект приложения перехватит любое исключение и выбросит
     * унифицированный тип.
     */
    public function testRunException()
    {
        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->never())->method('complete');

        $cleanUp = $this->getMockBuilder(Task::class)->getMock();
        $cleanUp->expects($this->once())->method('run')->with($this->equalTo($state));

        $task1 = $this->getMockBuilder(Task::class)->getMock();

        $task2 = $this->getMockBuilder(Task::class)->getMock();
        $task2->method('run')->will($this->throwException(new InvalidArgumentException()));

        $pipe = new ArrayPipe([$task1, $task2], $cleanUp);

        $this->expectException(PipeException::class);
        $pipe->run($state);
    }

    /**
     * Проверяет, что очередь пишет данные в лог.
     */
    public function testLogger()
    {
        $state = $this->getMockBuilder(State::class)->getMock();
        $task = $this->getMockBuilder(Task::class)->getMock();

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

        $pipe = new ArrayPipe([$task], null, $logger);
        $pipe->run($state);
    }

    /**
     * Проверяет, что очередь передаст объект лога в задачу, если требуется.
     */
    public function testLoggableTaskLoggerInjected()
    {
        $state = $this->getMockBuilder(State::class)->getMock();
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

        $pipe = new ArrayPipe([$task], null, $logger);
        $pipe->run($state);
    }
}

/**
 * Abstract mock class to test task with loggable task interface.
 */
abstract class ArrayPipeTestLoggableMock implements Task, LoggableTask
{
}
