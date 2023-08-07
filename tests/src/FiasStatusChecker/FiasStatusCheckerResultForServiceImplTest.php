<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasStatusChecker;

use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerResultForServiceImpl;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerService;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerStatus;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта с результатми проверки статуса ФИАС.
 *
 * @internal
 */
class FiasStatusCheckerResultForServiceImplTest extends BaseCase
{
    /**
     * Проверяет, что объект возвращает правильный статус.
     */
    public function testGetStatus(): void
    {
        $status = FiasStatusCheckerStatus::AVAILABLE;
        $service = FiasStatusCheckerService::FILE_SERVER;
        $reason = 'reason';

        $result = new FiasStatusCheckerResultForServiceImpl($status, $service, $reason);

        $this->assertSame($status, $result->getStatus());
    }

    /**
     * Проверяет, что объект возвращает правильный сервис.
     */
    public function testGetService(): void
    {
        $status = FiasStatusCheckerStatus::AVAILABLE;
        $service = FiasStatusCheckerService::FILE_SERVER;
        $reason = 'reason';

        $result = new FiasStatusCheckerResultForServiceImpl($status, $service, $reason);

        $this->assertSame($service, $result->getService());
    }

    /**
     * Проверяет, что объект возвращает правильную причину установки статуса.
     */
    public function testGetReason(): void
    {
        $status = FiasStatusCheckerStatus::AVAILABLE;
        $service = FiasStatusCheckerService::FILE_SERVER;
        $reason = 'reason';

        $result = new FiasStatusCheckerResultForServiceImpl($status, $service, $reason);

        $this->assertSame($reason, $result->getReason());
    }
}
