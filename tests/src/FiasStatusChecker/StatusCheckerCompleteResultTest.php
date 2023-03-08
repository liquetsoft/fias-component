<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasStatusChecker;

use Liquetsoft\Fias\Component\FiasStatusChecker\FiasServices;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatuses;
use Liquetsoft\Fias\Component\FiasStatusChecker\StatusCheckerCompleteResult;
use Liquetsoft\Fias\Component\FiasStatusChecker\StatusCheckerServiceResult;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта с результатми проверки статуса ФИАС.
 *
 * @internal
 */
class StatusCheckerCompleteResultTest extends BaseCase
{
    /**
     * Проверяет, что объект возвращает правильный статус.
     */
    public function testGetResultStatus(): void
    {
        $status = FiasStatuses::AVAILABLE;
        $perService = [];

        $result = new StatusCheckerCompleteResult($status, $perService);

        $this->assertSame($status, $result->getResultStatus());
    }

    /**
     * Проверяет, что объект возвращает правильный статус по каждому сервису.
     */
    public function testGetPerServiceStatuses(): void
    {
        $status = FiasStatuses::AVAILABLE;
        $perService = [
            new StatusCheckerServiceResult(FiasStatuses::AVAILABLE, FiasServices::FILE_SERVER),
        ];

        $result = new StatusCheckerCompleteResult($status, $perService);

        $this->assertSame($perService, $result->getPerServiceStatuses());
    }
}
