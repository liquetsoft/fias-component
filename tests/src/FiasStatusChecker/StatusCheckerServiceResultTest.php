<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasStatusChecker;

use Liquetsoft\Fias\Component\FiasStatusChecker\FiasServices;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatuses;
use Liquetsoft\Fias\Component\FiasStatusChecker\StatusCheckerServiceResult;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта с результатми проверки статуса ФИАС.
 *
 * @internal
 */
class StatusCheckerServiceResultTest extends BaseCase
{
    /**
     * Проверяет, что объект возвращает правильный статус.
     */
    public function testGetStatus(): void
    {
        $status = FiasStatuses::AVAILABLE;
        $service = FiasServices::FILE_SERVER;
        $reason = 'reason';

        $result = new StatusCheckerServiceResult($status, $service, $reason);

        $this->assertSame($status, $result->getStatus());
    }

    /**
     * Проверяет, что объект возвращает правильный сервис.
     */
    public function testGetService(): void
    {
        $status = FiasStatuses::AVAILABLE;
        $service = FiasServices::FILE_SERVER;
        $reason = 'reason';

        $result = new StatusCheckerServiceResult($status, $service, $reason);

        $this->assertSame($service, $result->getService());
    }

    /**
     * Проверяет, что объект возвращает правильную причину установки статуса.
     */
    public function testGetReason(): void
    {
        $status = FiasStatuses::AVAILABLE;
        $service = FiasServices::FILE_SERVER;
        $reason = 'reason';

        $result = new StatusCheckerServiceResult($status, $service, $reason);

        $this->assertSame($reason, $result->getReason());
    }
}
