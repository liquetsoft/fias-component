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
     * Создает мок для объекта состояния.
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
