<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\VersionSetTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;

/**
 * Тест для задачи, которая сохраняет текущую версию ФИАС.
 *
 * @internal
 */
class VersionSetTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект получает версию ФИАС и передает в менеджер версий.
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $version = $this->createFakeData()->numberBetween(1, 123);
        $url = $this->createFakeData()->url();

        $response = $this->mock(FiasInformerResponse::class);
        $response->method('getVersion')->willReturn($version);
        $response->method('getFullUrl')->willReturn($url);

        $state = $this->createDefaultStateMock(
            [
                StateParameter::FIAS_INFO => $response,
            ]
        );

        $versionManager = $this->mock(VersionManager::class);
        $versionManager->expects($this->once())
            ->method('setCurrentVersion')
            ->with(
                $this->equalTo($response)
            );

        $task = new VersionSetTask($versionManager);

        $task->run($state);
    }

    /**
     * Проверяет, что объект ничего не запишет, если параметра с результатом нет.
     *
     * @throws \Exception
     */
    public function testRunNoResultParameter(): void
    {
        $state = $this->createDefaultStateMock();

        $versionManager = $this->mock(VersionManager::class);
        $versionManager->expects($this->never())->method('setCurrentVersion');

        $task = new VersionSetTask($versionManager);

        $task->run($state);
    }
}
