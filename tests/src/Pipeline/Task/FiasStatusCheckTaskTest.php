<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusChecker;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerResult;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerResultForService;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerService;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerStatus;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\FiasStatusCheckTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

/**
 * Тест для задачи, которая проверяет доступность сервисов ФИАС.
 *
 * @internal
 */
class FiasStatusCheckTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;

    /**
     * Проверяет, что объект ничего не сделает, если ФИАС доступен.
     */
    public function testRunOk(): void
    {
        /** @var FiasStatusCheckerResult&MockObject */
        $result = $this->getMockBuilder(FiasStatusCheckerResult::class)->getMock();
        $result->expects($this->once())->method('canProceed')->willReturn(true);

        /** @var FiasStatusChecker&MockObject */
        $statusChecker = $this->getMockBuilder(FiasStatusChecker::class)->getMock();
        $statusChecker->expects($this->once())->method('check')->willReturn($result);

        $state = $this->createPipelineStateMock();

        $task = new FiasStatusCheckTask($statusChecker);
        $runResult = $task->run($state);

        $this->assertSame($state, $runResult);
    }

    /**
     * Проверяет, что объект залоггирует проблему и остановит папйлайн,
     * если ФИАС недоступен.
     */
    public function testRunFail(): void
    {
        $reason = 'test reason';

        /** @var FiasStatusCheckerResultForService&MockObject */
        $serviceResult = $this->getMockBuilder(FiasStatusCheckerResultForService::class)->getMock();
        $serviceResult->method('getService')->willReturn(FiasStatusCheckerService::FILE_SERVER);
        $serviceResult->method('getStatus')->willReturn(FiasStatusCheckerStatus::AVAILABLE);
        $serviceResult->method('getReason')->willReturn('');

        /** @var FiasStatusCheckerResultForService&MockObject */
        $serviceResult1 = $this->getMockBuilder(FiasStatusCheckerResultForService::class)->getMock();
        $serviceResult1->method('getService')->willReturn(FiasStatusCheckerService::INFORMER);
        $serviceResult1->method('getStatus')->willReturn(FiasStatusCheckerStatus::NOT_AVAILABLE);
        $serviceResult1->method('getReason')->willReturn($reason);

        /** @var FiasStatusCheckerResult&MockObject */
        $result = $this->getMockBuilder(FiasStatusCheckerResult::class)->getMock();
        $result->expects($this->once())->method('canProceed')->willReturn(false);
        $result->expects($this->once())->method('getPerServiceStatuses')->willReturn([$serviceResult, $serviceResult1]);

        $logger = $this->createLoggerMock();
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->identicalTo(LogLevel::INFO),
                $this->anything(),
                $this->identicalTo(
                    [
                        'statuses' => [
                            FiasStatusCheckerService::FILE_SERVER->value => [
                                'status' => FiasStatusCheckerStatus::AVAILABLE->value,
                                'reason' => '',
                            ],
                            FiasStatusCheckerService::INFORMER->value => [
                                'status' => FiasStatusCheckerStatus::NOT_AVAILABLE->value,
                                'reason' => $reason,
                            ],
                        ],
                    ]
                )
            );

        /** @var FiasStatusChecker&MockObject */
        $statusChecker = $this->getMockBuilder(FiasStatusChecker::class)->getMock();
        $statusChecker->expects($this->once())->method('check')->willReturn($result);

        $state = $this->createPipelineStateMock();
        $state->expects($this->once())
            ->method('with')
            ->with(
                $this->identicalTo(PipelineStateParam::INTERRUPT_PIPELINE),
                $this->identicalTo(true)
            )
            ->willReturnSelf();

        $task = new FiasStatusCheckTask($statusChecker);
        $task->injectLogger($logger);
        $runResult = $task->run($state);

        $this->assertSame($state, $runResult);
    }
}
