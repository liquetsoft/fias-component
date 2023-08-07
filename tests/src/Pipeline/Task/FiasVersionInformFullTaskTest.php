<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\FiasVersionInformFullTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

/**
 * Тест для задачи, которая получает информацию о полной версии ФИАС.
 *
 * @internal
 */
class FiasVersionInformFullTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;

    /**
     * Проверяет, что объект получит и вернет правильную версию ФИАС
     */
    public function testRun(): void
    {
        $version = 123;
        $archiveUrl = 'http://test.test/test';

        /** @var FiasInformerResponse&MockObject */
        $informerResponse = $this->getMockBuilder(FiasInformerResponse::class)->getMock();
        $informerResponse->method('getVersion')->willReturn($version);
        $informerResponse->method('getFullUrl')->willReturn($archiveUrl);

        /** @var FiasInformer&MockObject */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->expects($this->once())->method('getLatestVersion')->willReturn($informerResponse);

        $newState = $this->createPipelineStateMock();
        $state = $this->createPipelineStateMock();
        $state->expects($this->once())
            ->method('withList')
            ->with(
                $this->identicalTo(
                    [
                        PipelineStateParam::ARCHIVE_URL->value => $archiveUrl,
                        PipelineStateParam::PROCESSING_VERSION->value => $version,
                    ]
                )
            )
            ->willReturn($newState);

        $logger = $this->createLoggerMockExpectsMessage(
            LogLevel::INFO,
            'Full version was found',
            [
                PipelineStateParam::ARCHIVE_URL->value => $archiveUrl,
                PipelineStateParam::PROCESSING_VERSION->value => $version,
            ]
        );

        $task = new FiasVersionInformFullTask($informer);
        $task->injectLogger($logger);
        $stateToTest = $task->run($state);

        $this->assertSame($newState, $stateToTest);
    }
}
