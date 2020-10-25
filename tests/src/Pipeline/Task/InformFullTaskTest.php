<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\Task\InformFullTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая получает ссылку на полную версию ФИАС.
 */
class InformFullTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно получает ссылку.
     *
     * @throws Exception
     */
    public function testRun()
    {
        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->will($this->returnValue(true));
        $informerResult->method('getVersion')->will($this->returnValue(1));
        $informerResult->method('getUrl')->will($this->returnValue('http://test.test/test'));

        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getCompleteInfo')->will($this->returnValue($informerResult));

        $state = $this->createDefaultStateMock(
            [],
            [
                Task::FIAS_INFO_PARAM => $informerResult,
            ]
        );

        $task = new InformFullTask($informer);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если сервис информирования не вернет ответ.
     *
     * @throws Exception
     */
    public function testRunNoResponseException()
    {
        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->will($this->returnValue(false));

        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getCompleteInfo')->will($this->returnValue($informerResult));

        $state = $this->createDefaultStateMock();

        $task = new InformFullTask($informer);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
