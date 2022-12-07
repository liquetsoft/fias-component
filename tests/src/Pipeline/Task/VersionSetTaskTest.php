<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Pipeline\Task\VersionSetTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;
use PHPUnit\Framework\MockObject\MockObject;

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

        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('getVersion')->willReturn($version);
        $response->method('getUrl')->willReturn($url);
        $response->method('hasResult')->willReturn(true);

        $state = $this->createDefaultStateMock(
            [
                Task::FIAS_INFO_PARAM => $response,
            ]
        );

        /** @var MockObject&VersionManager */
        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->expects($this->once())
            ->method('setCurrentVersion')
            ->with(
                $this->equalTo($response)
            );

        $task = new VersionSetTask($versionManager);

        $task->run($state);
    }

    /**
     * Проверяет, что объект ничего не запишет, если результата в ответе нет.
     *
     * @throws \Exception
     */
    public function testRunNoResult(): void
    {
        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('hasResult')->willReturn(false);

        $state = $this->createDefaultStateMock(
            [
                Task::FIAS_INFO_PARAM => $response,
            ]
        );

        /** @var MockObject&VersionManager */
        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->expects($this->never())->method('setCurrentVersion');

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

        /** @var MockObject&VersionManager */
        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->expects($this->never())->method('setCurrentVersion');

        $task = new VersionSetTask($versionManager);

        $task->run($state);
    }
}
