<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Pipeline\Task\VersionGetTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;

/**
 * Тест для задачи, которая получает текущую версию ФИАС.
 *
 * @internal
 */
class VersionGetTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект получает версию ФИАС и записывает в состояние.
     *
     * @throws Exception
     */
    public function testRun(): void
    {
        $version = $this->createFakeData()->numberBetween(1, 123);
        $url = $this->createFakeData()->url;

        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('getVersion')->willReturn($version);
        $response->method('getUrl')->willReturn($url);
        $response->method('hasResult')->willReturn(true);

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->method('getCurrentVersion')->willReturn($response);

        $state = new ArrayState();

        $task = new VersionGetTask($versionManager);
        $task->run($state);

        $this->assertSame($version, $state->getParameter(Task::FIAS_VERSION_PARAM));
    }

    /**
     * Проверяет, что объект выбросит исключение, если ФИАС не установлен.
     *
     * @throws Exception
     */
    public function testRunNoResultException(): void
    {
        $response = $this->getMockBuilder(InformerResponse::class)->getMock();
        $response->method('hasResult')->willReturn(false);

        $versionManager = $this->getMockBuilder(VersionManager::class)->getMock();
        $versionManager->method('getCurrentVersion')->willReturn($response);

        $state = $this->createDefaultStateMock();

        $task = new VersionGetTask($versionManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
