<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Pipeline\Task\VersionSetTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;

/**
 * Тест для задачи, которая сохраняет текущую версию ФИАС.
 */
class VersionSetTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект получает версию ФИАС и передает в менеджер версий.
     */
    public function testRun()
    {
        $version = $this->createFakeData()->numberBetween(1, 123);
        $url = $this->createFakeData()->url;

        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('getVersion')->will($this->returnValue($version));
        $response->method('getUrl')->will($this->returnValue($url));
        $response->method('hasResult')->will($this->returnValue(true));

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->once())->method('getParameter')->will($this->returnCallback(function ($name) use ($response) {
            return $name === Task::FIAS_INFO_PARAM ? $response : null;
        }));

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->expects($this->once())->method('setCurrentVersion')->with($this->equalTo($response));

        $task = new VersionSetTask($versionManager);
        $task->run($state);
    }

    /**
     * Проверяет, что объект ничего не запишет, если результата в ответе нет.
     */
    public function testRunNoResult()
    {
        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('hasResult')->will($this->returnValue(false));

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->once())->method('getParameter')->will($this->returnCallback(function ($name) use ($response) {
            return $name === Task::FIAS_INFO_PARAM ? $response : null;
        }));

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->expects($this->never())->method('setCurrentVersion');

        $task = new VersionSetTask($versionManager);
        $task->run($state);
    }

    /**
     * Проверяет, что объект ничего не запишет, если параметра с результатом нет.
     */
    public function testRunNoResultParameter()
    {
        $state = $this->getMockBuilder(State::class)->getMock();

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->expects($this->never())->method('setCurrentVersion');

        $task = new VersionSetTask($versionManager);
        $task->run($state);
    }
}
