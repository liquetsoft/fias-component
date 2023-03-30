<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Трэйт, который содержит методы для создания моков логгера.
 */
trait LoggerCase
{
    /**
     * Создает мок для объекта лога, который ожидает одно сообщение.
     *
     * @return LoggerInterface&MockObject
     */
    public function createLoggerMockExpectsMessage(string $logLevel, string $message, array $context = []): LoggerInterface
    {
        $logger = $this->createLoggerMock();

        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->identicalTo($logLevel),
                $this->callback(
                    fn (string $logMessage): bool => str_contains($logMessage, $message)
                ),
                $this->callback(
                    function (array $logContext) use ($context): bool {
                        ksort($logContext);
                        ksort($context);

                        return $logContext === $context;
                    }
                )
            );

        return $logger;
    }

    /**
     * Создает мок для объекта лога.
     *
     * @return LoggerInterface&MockObject
     */
    public function createLoggerMock(): LoggerInterface
    {
        /** @var LoggerInterface&MockObject */
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        return $logger;
    }
}
