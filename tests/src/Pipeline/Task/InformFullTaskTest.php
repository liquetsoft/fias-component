<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\Task\InformFullTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;

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
     * @throws Exception
     */
    public function testRun(): void
    {
        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->willReturn(true);
        $informerResult->method('getVersion')->willReturn(1);
        $informerResult->method('getUrl')->willReturn('http://test.test/test');

        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getCompleteInfo')->willReturn($informerResult);

        $state = new ArrayState();

        $task = new InformFullTask($informer);
        $task->run($state);

        $this->assertSame($informerResult, $state->getParameter(Task::FIAS_INFO_PARAM));
    }

    /**
     * Проверяет, что объект выбросит исключение, если сервис информирования не вернет ответ.
     *
     * @throws Exception
     */
    public function testRunNoResponseException(): void
    {
        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->willReturn(false);

        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getCompleteInfo')->willReturn($informerResult);

        $state = $this->createDefaultStateMock();

        $task = new InformFullTask($informer);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
