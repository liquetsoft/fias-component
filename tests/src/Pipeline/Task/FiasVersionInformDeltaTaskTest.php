<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\FiasVersionInformDeltaTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

/**
 * Тест для задачи, которая получает информацию о пследующем обновлении ФИАС.
 *
 * @internal
 */
class FiasVersionInformDeltaTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;

    /**
     * Проверяет, что объект получит и вернет правильную версию ФИАС
     */
    public function testRun(): void
    {
        $installedVersion = 122;
        $version = 123;
        $archiveUrl = 'http://test.test/test';

        /** @var FiasInformerResponse&MockObject */
        $informerResponse = $this->getMockBuilder(FiasInformerResponse::class)->getMock();
        $informerResponse->method('getVersion')->willReturn($version);
        $informerResponse->method('getDeltaUrl')->willReturn($archiveUrl);

        /** @var FiasInformer&MockObject */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->expects($this->once())
            ->method('getNextVersion')
            ->with(
                $this->identicalTo($installedVersion)
            )
            ->willReturn($informerResponse);

        $newState = $this->createPipelineStateMock();
        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::INSTALLED_VERSION->value => $installedVersion,
            ]
        );
        $state->expects($this->once())
            ->method('withList')
            ->with(
                $this->identicalTo(
                    [
                        PipelineStateParam::ARCHIVE_URL->value => $archiveUrl,
                        PipelineStateParam::PROCESSING_VERSION->value => $version,
                        PipelineStateParam::INSTALLED_VERSION->value => $installedVersion,
                    ]
                )
            )
            ->willReturn($newState);

        $logger = $this->createLoggerMockExpectsMessage(
            LogLevel::INFO,
            'Delta version',
            [
                PipelineStateParam::ARCHIVE_URL->value => $archiveUrl,
                PipelineStateParam::PROCESSING_VERSION->value => $version,
                PipelineStateParam::INSTALLED_VERSION->value => $installedVersion,
            ]
        );

        $task = new FiasVersionInformDeltaTask($informer);
        $task->injectLogger($logger);
        $stateToTest = $task->run($state);

        $this->assertSame($newState, $stateToTest);
    }

    /**
     * Проверяет, что объект прервет исполнение, если обновлений нет.
     */
    public function testRunUpToDate(): void
    {
        $installedVersion = 122;

        /** @var FiasInformer&MockObject */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->expects($this->once())
            ->method('getNextVersion')
            ->with(
                $this->identicalTo($installedVersion)
            )
            ->willReturn(null);

        $newState = $this->createPipelineStateMock();
        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::INSTALLED_VERSION->value => $installedVersion,
            ]
        );
        $state->expects($this->once())
            ->method('with')
            ->with(
                $this->identicalTo(PipelineStateParam::INTERRUPT_PIPELINE),
                $this->identicalTo(true)
            )
            ->willReturn($newState);

        $logger = $this->createLoggerMockExpectsMessage(
            LogLevel::INFO,
            'up to date'
        );

        $task = new FiasVersionInformDeltaTask($informer);
        $task->injectLogger($logger);
        $stateToTest = $task->run($state);

        $this->assertSame($newState, $stateToTest);
    }
}
