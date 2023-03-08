<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\InformDeltaTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @throws \Exception
     */
    public function testRun(): void
    {
        $version = 123;

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('getVersion')->willReturn($version);
        $informerResult->method('getDeltaUrl')->willReturn('http://test.test/test');

        /** @var MockObject&FiasInformer */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getNextVersion')->with($this->equalTo($version))->willReturn($informerResult);

        $state = new ArrayState();
        $state->setAndLockParameter(StateParameter::FIAS_VERSION, $version);

        $task = new InformDeltaTask($informer);
        $task->run($state);

        $this->assertSame($informerResult, $state->getParameter(StateParameter::FIAS_INFO));
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указана текущая версия ФИАС.
     *
     * @throws \Exception
     */
    public function testRunNoVersionException(): void
    {
        /** @var MockObject&FiasInformer */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->expects($this->never())->method('getNextVersion');

        $state = $this->createDefaultStateMock();

        $task = new InformDeltaTask($informer);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект прервет цепочку задач, если не найдется обновлений.
     *
     * @throws \Exception
     */
    public function testRunNoResponseComplete(): void
    {
        $version = 123;

        /** @var MockObject&FiasInformer */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getNextVersion')->with($this->equalTo($version))->willReturn(null);

        $state = new ArrayState();
        $state->setAndLockParameter(StateParameter::FIAS_VERSION, $version);

        $task = new InformDeltaTask($informer);
        $task->run($state);

        $this->assertNull($state->getParameter(StateParameter::FIAS_INFO));
    }
}
