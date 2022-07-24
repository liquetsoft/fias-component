<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Pipeline\Task\LoggableTask;
use Liquetsoft\Fias\Component\Pipeline\Task\LoggableTaskTrait;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Тест для трэйта, который обеспечивает логгирование.
 *
 * @internal
 */
class LoggableTaskTraitTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно залоггирует сообщение.
     *
     * @throws Exception
     *
     * @psalm-suppress UndefinedInterfaceMethod
     * @psalm-suppress InvalidScalarArgument
     */
    public function testLog(): void
    {
        $logLevel = 'ERROR';
        $message = 'test';
        $context = ['context' => 'context'];
        $defaultContext = ['default_context' => 'default_context'];

        /** @var MockObject&LoggerInterface */
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->identicalTo($logLevel),
                $this->identicalTo($message),
                $this->identicalTo(array_merge($defaultContext, $context))
            );

        /** @var MockObject&LoggableTask */
        $loggableTask = $this->getMockForTrait(LoggableTaskTrait::class);
        $loggableTask->injectLogger($logger, $defaultContext);
        $loggableTask->log($logLevel, $message, $context);
    }
}
