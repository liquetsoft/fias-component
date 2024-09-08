<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\StatusCheckerException;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusChecker;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerResult;
use Liquetsoft\Fias\Component\Pipeline\Task\CheckStatusTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая проверяет текущий статус ФИАС.
 *
 * @internal
 */
final class CheckStatusTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача проверит статус ФИАС.
     */
    public function testRun(): void
    {
        $checkerResult = $this->mock(FiasStatusCheckerResult::class);
        $checkerResult->expects($this->any())
            ->method('canProceed')
            ->willReturn(true);

        $statusChecker = $this->mock(FiasStatusChecker::class);
        $statusChecker->expects($this->once())
            ->method('check')
            ->willReturn($checkerResult);

        $state = $this->createDefaultStateMock();

        $task = new CheckStatusTask($statusChecker);
        $task->run($state);
    }

    /**
     * Проверяет, что задача проверит статус ФИАС и выбросит исключение,
     * если он недоступен.
     */
    public function testRunException(): void
    {
        $checkerResult = $this->mock(FiasStatusCheckerResult::class);
        $checkerResult->expects($this->any())
            ->method('canProceed')
            ->willReturn(false);
        $checkerResult->expects($this->any())
            ->method('getPerServiceStatuses')
            ->willReturn([]);

        $statusChecker = $this->mock(FiasStatusChecker::class);
        $statusChecker->expects($this->once())
            ->method('check')
            ->willReturn($checkerResult);

        $state = $this->createDefaultStateMock();

        $task = new CheckStatusTask($statusChecker);

        $this->expectException(StatusCheckerException::class);
        $task->run($state);
    }
}
