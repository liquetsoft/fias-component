<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\Task\InformDeltaTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая получает ссылку на частичную версию ФИАС.
 *
 * @internal
 */
class InformDeltaTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно получает ссылку.
     *
     * @throws Exception
     */
    public function testRun(): void
    {
        $version = 123;

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->willReturn(true);
        $informerResult->method('getVersion')->willReturn($version);
        $informerResult->method('getUrl')->willReturn('http://test.test/test');

        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getDeltaInfo')->with($this->equalTo($version))->willReturn($informerResult);

        $state = new ArrayState();
        $state->setAndLockParameter(Task::FIAS_VERSION_PARAM, $version);

        $task = new InformDeltaTask($informer);
        $task->run($state);

        $this->assertSame($informerResult, $state->getParameter(Task::FIAS_INFO_PARAM));
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указана текущая версия ФИАС.
     *
     * @throws Exception
     */
    public function testRunNoVersionException(): void
    {
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->expects($this->never())->method('getDeltaInfo');

        $state = $this->createDefaultStateMock();

        $task = new InformDeltaTask($informer);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект прервет цепочку задач, если не найдется обновлений.
     *
     * @throws Exception
     */
    public function testRunNoResponseComplete(): void
    {
        $version = 123;

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->willReturn(false);

        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getDeltaInfo')->with($this->equalTo($version))->willReturn($informerResult);

        $state = new ArrayState();
        $state->setAndLockParameter(Task::FIAS_VERSION_PARAM, $version);

        $task = new InformDeltaTask($informer);
        $task->run($state);

        $this->assertSame($informerResult, $state->getParameter(Task::FIAS_INFO_PARAM));
    }
}
