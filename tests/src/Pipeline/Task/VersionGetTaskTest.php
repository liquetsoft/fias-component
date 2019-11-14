<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\State;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Pipeline\Task\VersionGetTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;

/**
 * Тест для задачи, которая получает текущую версию ФИАС.
 */
class VersionGetTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект получает версию ФИАС и записывает в состояние.
     */
    public function testRun()
    {
        $version = $this->createFakeData()->numberBetween(1, 123);
        $url = $this->createFakeData()->url;

        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('getVersion')->will($this->returnValue($version));
        $response->method('getUrl')->will($this->returnValue($url));
        $response->method('hasResult')->will($this->returnValue(true));

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->method('getCurrentVersion')->will($this->returnValue($response));

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->once())->method('setAndLockParameter')->with(
            $this->equalTo(Task::FIAS_VERSION_PARAM),
            $this->equalTo($version)
        );

        $task = new VersionGetTask($versionManager);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если ФИАС не установлен.
     */
    public function testRunNoResultException()
    {
        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('hasResult')->will($this->returnValue(false));

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->method('getCurrentVersion')->will($this->returnValue($response));

        $state = $this->getMockBuilder(State::class)->getMock();

        $task = new VersionGetTask($versionManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
