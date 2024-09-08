<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasStatusChecker;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerImpl;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerService;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerStatus;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который проверяет статус сервисов ФИАС.
 *
 * @internal
 */
final class FiasStatusCheckerImplTest extends BaseCase
{
    /**
     * Проверяет, что объект возвращает правильный статус, если оба сервиса доступны.
     */
    public function testCheck(): void
    {
        $versionUrl = 'https://test.test/version';

        $informerResult = $this->mock(FiasInformerResponse::class);
        $informerResult->method('getFullUrl')->willReturn($versionUrl);

        $informer = $this->mock(FiasInformer::class);
        $informer->expects($this->exactly(2))
            ->method('getLatestVersion')
            ->willReturn($informerResult);

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method('head')
            ->with($this->equalTo($versionUrl))
            ->willReturn($this->createOkResponseMock());

        $checker = new FiasStatusCheckerImpl($transport, $informer);
        $checkResult = $checker->check();
        $servicesStatuses = $checkResult->getPerServiceStatuses();

        $this->assertSame(FiasStatusCheckerStatus::AVAILABLE, $checkResult->getResultStatus());
        $this->assertCount(2, $servicesStatuses);
        $this->assertSame(FiasStatusCheckerStatus::AVAILABLE, $servicesStatuses[0]->getStatus());
        $this->assertSame(FiasStatusCheckerService::INFORMER, $servicesStatuses[0]->getService());
        $this->assertSame(FiasStatusCheckerStatus::AVAILABLE, $servicesStatuses[1]->getStatus());
        $this->assertSame(FiasStatusCheckerService::FILE_SERVER, $servicesStatuses[1]->getService());
    }

    /**
     * Проверяет, что объект возвращает правильный статус, если сервис информирования недоступен.
     */
    public function testCheckInformerUnavailable(): void
    {
        $informer = $this->mock(FiasInformer::class);
        $informer->method('getLatestVersion')->willThrowException(new \RuntimeException());

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->never())->method('head');

        $checker = new FiasStatusCheckerImpl($transport, $informer);
        $checkResult = $checker->check();
        $servicesStatuses = $checkResult->getPerServiceStatuses();

        $this->assertSame(FiasStatusCheckerStatus::NOT_AVAILABLE, $checkResult->getResultStatus());
        $this->assertCount(2, $servicesStatuses);
        $this->assertSame(FiasStatusCheckerStatus::NOT_AVAILABLE, $servicesStatuses[0]->getStatus());
        $this->assertSame(FiasStatusCheckerService::INFORMER, $servicesStatuses[0]->getService());
        $this->assertSame(FiasStatusCheckerStatus::UNKNOWN, $servicesStatuses[1]->getStatus());
        $this->assertSame(FiasStatusCheckerService::FILE_SERVER, $servicesStatuses[1]->getService());
    }

    /**
     * Проверяет, что объект возвращает правильный статус, если файловый сервер недоступен.
     */
    public function testCheckFileServerUnavailable(): void
    {
        $versionUrl = 'https://test.test/version';

        $informerResult = $this->mock(FiasInformerResponse::class);
        $informerResult->method('getFullUrl')->willReturn($versionUrl);

        $informer = $this->mock(FiasInformer::class);
        $informer->expects($this->exactly(2))
            ->method('getLatestVersion')
            ->willReturn($informerResult);

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method('head')
            ->with($this->equalTo($versionUrl))
            ->willReturn($this->createBadResponseMock());

        $checker = new FiasStatusCheckerImpl($transport, $informer);
        $checkResult = $checker->check();
        $servicesStatuses = $checkResult->getPerServiceStatuses();

        $this->assertSame(FiasStatusCheckerStatus::NOT_AVAILABLE, $checkResult->getResultStatus());
        $this->assertCount(2, $servicesStatuses);
        $this->assertSame(FiasStatusCheckerStatus::AVAILABLE, $servicesStatuses[0]->getStatus());
        $this->assertSame(FiasStatusCheckerService::INFORMER, $servicesStatuses[0]->getService());
        $this->assertSame(FiasStatusCheckerStatus::NOT_AVAILABLE, $servicesStatuses[1]->getStatus());
        $this->assertSame(FiasStatusCheckerService::FILE_SERVER, $servicesStatuses[1]->getService());
    }
}
