<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasStatusChecker;

use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerResultForService;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerResultImpl;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerStatus;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта с результатми проверки статуса ФИАС.
 *
 * @internal
 */
final class FiasStatusCheckerResultImplTest extends BaseCase
{
    /**
     * Проверяет, что объект возвращает правильный статус.
     */
    public function testGetResultStatus(): void
    {
        $status = FiasStatusCheckerStatus::AVAILABLE;
        $perService = [];

        $result = new FiasStatusCheckerResultImpl($status, $perService);

        $this->assertSame($status, $result->getResultStatus());
    }

    /**
     * Проверяет, что объект возвращает правильный статус по каждому сервису.
     */
    public function testGetPerServiceStatuses(): void
    {
        $status = FiasStatusCheckerStatus::AVAILABLE;

        $perService = [
            $this->getMockBuilder(FiasStatusCheckerResultForService::class)->getMock(),
        ];

        $result = new FiasStatusCheckerResultImpl($status, $perService);

        $this->assertSame($perService, $result->getPerServiceStatuses());
    }

    /**
     * Проверяет, что объект возвращает правду, если загрузка доступна.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanProceed')]
    public function testCanProceed(FiasStatusCheckerStatus $status, bool $awaits): void
    {
        $result = new FiasStatusCheckerResultImpl($status, []);

        $this->assertSame($awaits, $result->canProceed());
    }

    public static function provideCanProceed(): array
    {
        return [
            'can proceed' => [
                FiasStatusCheckerStatus::AVAILABLE,
                true,
            ],
            "can't proceed" => [
                FiasStatusCheckerStatus::NOT_AVAILABLE,
                false,
            ],
            'unknown proceed' => [
                FiasStatusCheckerStatus::UNKNOWN,
                false,
            ],
        ];
    }
}
