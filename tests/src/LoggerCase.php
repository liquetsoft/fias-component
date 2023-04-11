<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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
        return $this->createLoggerMockExpectsMessages(
            [
                [
                    'level' => $logLevel,
                    'message' => $message,
                    'context' => $context,
                ],
            ]
        );
    }

    /**
     * Создает мок для объекта лога, который ожидает одно сообщение из списка.
     *
     * @return LoggerInterface&MockObject
     *
     * @psalm-suppress MixedArrayAccess
     */
    public function createLoggerMockExpectsMessages(array $messages): LoggerInterface
    {
        $preparedMessages = [];
        foreach ($messages as $message) {
            $context = (array) ($message['context'] ?? []);
            ksort($context);
            $preparedMessages[] = [
                'level' => (string) ($message['level'] ?? LogLevel::INFO),
                'message' => \is_string($message) ? $message : (string) ($message['message'] ?? ''),
                'context' => $context,
            ];
        }

        $logger = $this->createLoggerMock();
        $logger->expects($this->exactly(\count($preparedMessages)))
            ->method('log')
            ->willReturnCallback(
                function (string $level, string $message, array $context) use ($preparedMessages): void {
                    $existedMessage = array_filter(
                        $preparedMessages,
                        fn (array $m): bool => $m['level'] === $level
                            && $m['message'] === $message
                            && $this->contextesCompliment($m['context'], $context)
                    );
                    if (empty($existedMessage)) {
                        throw new \RuntimeException("Log message not allowed: {$message}");
                    }
                }
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

    /**
     * Проверяет, что второй массив содержит все элементы первого массива.
     */
    private function contextesCompliment(array $array1, array $array2): bool
    {
        $doesContain = true;

        foreach ($array1 as $key => $value1) {
            if (!isset($array2[$key])) {
                $doesContain = false;
                break;
            }
            $value2 = $array2[$key];
            if ($value1 !== $value2) {
                $doesContain = false;
                break;
            }
        }

        return $doesContain;
    }
}
