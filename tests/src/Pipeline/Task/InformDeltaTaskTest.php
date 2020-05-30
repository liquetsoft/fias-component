<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\InformDeltaTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая получает ссылку на частичную версию ФИАС.
 */
class InformDeltaTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно получает ссылку.
     */
    public function testRun()
    {
        $version = 123;

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->will($this->returnValue(true));
        $informerResult->method('getVersion')->will($this->returnValue($version));
        $informerResult->method('getUrl')->will($this->returnValue('http://test.test/test'));

        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getDeltaInfo')->with($this->equalTo($version))->will($this->returnValue($informerResult));

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($version) {
            return $name === Task::FIAS_VERSION_PARAM ? $version : null;
        }));
        $state->expects($this->once())->method('setAndLockParameter')->with(
            $this->equalTo(Task::FIAS_INFO_PARAM),
            $this->equalTo($informerResult)
        );

        $task = new InformDeltaTask($informer);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указана текущая версия ФИАС.
     */
    public function testRunNoVersionException()
    {
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->expects($this->never())->method('getDeltaInfo');

        $state = $this->getMockBuilder(State::class)->getMock();

        $task = new InformDeltaTask($informer);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект прервет цепочку задач, если не найдется обновлений.
     */
    public function testRunNoResponseComplete()
    {
        $version = 123;

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->will($this->returnValue(false));

        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getDeltaInfo')->with($this->equalTo($version))->will($this->returnValue($informerResult));

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($version) {
            return $name === Task::FIAS_VERSION_PARAM ? $version : null;
        }));
        $state->expects($this->once())->method('complete');
        $state->expects($this->once())->method('setAndLockParameter')->with(
            $this->equalTo(Task::FIAS_INFO_PARAM),
            $this->equalTo($informerResult)
        );

        $task = new InformDeltaTask($informer);
        $task->run($state);
    }
}
