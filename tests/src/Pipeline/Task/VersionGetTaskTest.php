<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\VersionGetTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;

/**
 * Тест для задачи, которая получает текущую версию ФИАС.
 *
 * @internal
 */
final class VersionGetTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект получает версию ФИАС и записывает в состояние.
     */
    public function testRun(): void
    {
        $version = 10;
        $url = 'https://test.test/test';

        $response = $this->mock(FiasInformerResponse::class);
        $response->method('getVersion')->willReturn($version);
        $response->method('getFullUrl')->willReturn($url);

        $versionManager = $this->mock(VersionManager::class);
        $versionManager->method('getCurrentVersion')->willReturn($response);

        $state = $this->createStateMock();

        $task = new VersionGetTask($versionManager);
        $newState = $task->run($state);
        $res = $newState->getParameter(StateParameter::FIAS_VERSION_NUMBER);

        $this->assertSame($version, $res);
    }

    /**
     * Проверяет, что объект выбросит исключение, если ФИАС не установлен.
     */
    public function testRunNoResultException(): void
    {
        $versionManager = $this->mock(VersionManager::class);
        $versionManager->method('getCurrentVersion')->willReturn(null);

        $state = $this->createStateMock();

        $task = new VersionGetTask($versionManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
