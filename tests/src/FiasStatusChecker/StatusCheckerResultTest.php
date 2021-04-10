<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasStatusChecker;

use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusChecker;
use Liquetsoft\Fias\Component\FiasStatusChecker\StatusCheckerResult;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта с результатми проверки статуса ФИАС.
 *
 * @internal
 */
class StatusCheckerResultTest extends BaseCase
{
    /**
     * Проверяет, что объект передает правильный статус.
     */
    public function testGetResultStatus(): void
    {
        $status = FiasStatusChecker::STATUS_NOT_AVAILABLE;
        $perService = [
            [
                'service' => FiasStatusChecker::SERVICE_INFORMER,
                'status' => FiasStatusChecker::STATUS_NOT_AVAILABLE,
                'reason' => 'WSDL file is unavailable',
            ],
        ];

        $result = new StatusCheckerResult($status, $perService);

        $this->assertSame($status, $result->getResultStatus());
    }

    /**
     * Проверяет, что объект передает правильный по каждому сервису.
     */
    public function testGetPerServiceStatuses(): void
    {
        $status = FiasStatusChecker::STATUS_NOT_AVAILABLE;
        $perService = [
            [
                'service' => FiasStatusChecker::SERVICE_INFORMER,
                'status' => FiasStatusChecker::STATUS_NOT_AVAILABLE,
                'reason' => 'WSDL file is unavailable',
            ],
        ];

        $result = new StatusCheckerResult($status, $perService);

        $this->assertSame($perService, $result->getPerServiceStatuses());
    }
}
