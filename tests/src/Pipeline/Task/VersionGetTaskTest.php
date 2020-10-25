<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

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
     *
     * @throws \Exception
     */
    public function testRun()
    {
        $version = $this->createFakeData()->numberBetween(1, 123);
        $url = $this->createFakeData()->url;

        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('getVersion')->willReturn($version);
        $response->method('getUrl')->willReturn($url);
        $response->method('hasResult')->willReturn(true);

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->method('getCurrentVersion')->will($this->returnValue($response));
        $versionManager = $this->checkAndReturnVersionManager($versionManager);

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->expects($this->once())
            ->method('setAndLockParameter')
            ->with(
                $this->equalTo(Task::FIAS_VERSION_PARAM),
                $this->equalTo($version)
            );
        $state = $this->checkAndReturnState($state);

        $task = new VersionGetTask($versionManager);

        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если ФИАС не установлен.
     *
     * @throws \Exception
     */
    public function testRunNoResultException()
    {
        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('hasResult')->willReturn(false);

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->method('getCurrentVersion')->willReturn($response);
        $versionManager = $this->checkAndReturnVersionManager($versionManager);

        $state = $this->getMockBuilder(State::class)->getMock();
        $state = $this->checkAndReturnState($state);

        $task = new VersionGetTask($versionManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
