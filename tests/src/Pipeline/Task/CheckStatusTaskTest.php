<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Exception\StatusCheckerException;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusChecker;
use Liquetsoft\Fias\Component\FiasStatusChecker\StatusCheckerResult;
use Liquetsoft\Fias\Component\Pipeline\Task\CheckStatusTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для задачи, которая проверяет текущий статус ФИАС.
 *
 * @internal
 */
class CheckStatusTaskTest extends BaseCase
{
    /**
     * Проверяет, что задача проверит статус ФИАС.
     *
     * @throws Exception
     */
    public function testRun(): void
    {
        $checkerResult = $this->getMockBuilder(StatusCheckerResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $checkerResult->method('getResultStatus')
            ->willReturn(FiasStatusChecker::STATUS_AVAILABLE);

        /** @var MockObject&FiasStatusChecker */
        $statusChecker = $this->getMockBuilder(FiasStatusChecker::class)->getMock();
        $statusChecker->expects($this->once())->method('check')->willReturn($checkerResult);

        $state = $this->createDefaultStateMock();

        $task = new CheckStatusTask($statusChecker);
        $task->run($state);
    }

    /**
     * Проверяет, что задача проверит статус ФИАС и выбросит исключение,
     * если он недоступен.
     *
     * @throws Exception
     */
    public function testRunException(): void
    {
        $checkerResult = $this->getMockBuilder(StatusCheckerResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $checkerResult->method('getResultStatus')
            ->willReturn(FiasStatusChecker::STATUS_NOT_AVAILABLE);

        /** @var MockObject&FiasStatusChecker */
        $statusChecker = $this->getMockBuilder(FiasStatusChecker::class)->getMock();
        $statusChecker->expects($this->once())->method('check')->willReturn($checkerResult);

        $state = $this->createDefaultStateMock();

        $task = new CheckStatusTask($statusChecker);

        $this->expectException(StatusCheckerException::class);
        $task->run($state);
    }
}
