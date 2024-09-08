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
final class VersionSetTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект получает версию ФИАС и передает в менеджер версий.
     */
    public function testRun(): void
    {
        $version = 123;

        $state = $this->createDefaultStateMock(
            [
                StateParameter::FIAS_NEXT_VERSION_NUMBER->value => $version,
            ]
        );

        $versionManager = $this->mock(VersionManager::class);
        $versionManager->expects($this->once())
            ->method('setCurrentVersion')
            ->with(
                $this->callback(
                    fn (FiasInformerResponse $r): bool => $r->getVersion() === $version
                )
            );

        $task = new VersionSetTask($versionManager);

        $task->run($state);
    }

    /**
     * Проверяет, что объект ничего не запишет, если параметра с результатом нет.
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
