<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\InformFullTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для задачи, которая получает ссылку на полную версию ФИАС.
 *
 * @internal
 */
class InformFullTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно получает ссылку.
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('getVersion')->willReturn(1);
        $informerResult->method('getFullUrl')->willReturn('http://test.test/test');

        /** @var MockObject&FiasInformer */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getLatestVersion')->willReturn($informerResult);

        $state = new ArrayState();

        $task = new InformFullTask($informer);
        $task->run($state);

        $this->assertSame($informerResult, $state->getParameter(StateParameter::FIAS_INFO));
    }
}
