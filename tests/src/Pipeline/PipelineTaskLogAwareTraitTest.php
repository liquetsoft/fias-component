<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline;

use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

/**
 * Тест для трэйта объекта операции, в которую можно передать объект логгера.
 *
 * @internal
 */
class PipelineTaskLogAwareTraitTest extends BaseCase
{
    use LoggerCase;

    /**
     * Проверяет, что объект залоггирует значения.
     *
     * @psalm-suppress InvalidArgument
     */
    public function testLog(): void
    {
        $logLevel = LogLevel::ERROR;
        $message = 'log message test';
        $context = ['context_param' => 'context param value'];

        $logger = $this->createLoggerMock();
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo($logLevel),
                $this->equalTo($message),
                $this->equalTo($context)
            );

        /** @var PipelineTaskLogAware&MockObject */
        $task = $this->getMockForTrait(PipelineTaskLogAwareTrait::class);
        $task->injectLogger($logger);
        $task->log($logLevel, $message, $context);
    }

    /**
     * Проверяет, что объект залоггирует значения, используя контекст по умолчанию.
     *
     * @psalm-suppress InvalidArgument
     */
    public function testLogWithDefaultContext(): void
    {
        $logLevel = LogLevel::ERROR;
        $message = 'log message test';
        $context = [
            'context_param' => 'context param value',
            'existed_param' => 'existed param from context',
        ];
        $defaultContext = [
            'default_context_param' => 'default context param value',
            'existed_param' => 'existed param from default',
        ];

        $logger = $this->createLoggerMock();
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo($logLevel),
                $this->equalTo($message),
                $this->equalTo(array_merge($defaultContext, $context))
            );

        /** @var PipelineTaskLogAware&MockObject */
        $task = $this->getMockForTrait(PipelineTaskLogAwareTrait::class);
        $task->injectLogger($logger, $defaultContext);
        $task->log($logLevel, $message, $context);
    }

    /**
     * Проверяет, что объект залоггирует значения.
     *
     * @psalm-suppress InvalidArgument
     */
    public function testLogInfo(): void
    {
        $message = 'log message test';
        $context = ['context_param' => 'context param value'];

        $logger = $this->createLoggerMock();
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo(LogLevel::INFO),
                $this->equalTo($message),
                $this->equalTo($context)
            );

        /** @var PipelineTaskLogAware&MockObject */
        $task = $this->getMockForTrait(PipelineTaskLogAwareTrait::class);
        $task->injectLogger($logger);
        $task->logInfo($message, $context);
    }
}
