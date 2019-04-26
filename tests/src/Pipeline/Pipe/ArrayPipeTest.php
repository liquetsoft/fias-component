<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Pipe;

use Liquetsoft\Fias\Component\Pipeline\Pipe\ArrayPipe;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Exception\PipeException;
use InvalidArgumentException;

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

        $cleanUp = $this->getMockBuilder(Task::class)->getMock();
        $cleanUp->expects($this->once())->method('run')->with($this->equalTo($state));

        $task1 = $this->getMockBuilder(Task::class)->getMock();

        $task2 = $this->getMockBuilder(Task::class)->getMock();
        $task2->method('run')->will($this->throwException(new InvalidArgumentException));

        $pipe = new ArrayPipe([$task1, $task2], $cleanUp);

        $this->expectException(PipeException::class);
        $pipe->run($state);
    }
}
