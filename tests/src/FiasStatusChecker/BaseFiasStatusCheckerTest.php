<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasStatusChecker;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;
use Liquetsoft\Fias\Component\FiasStatusChecker\BaseFiasStatusChecker;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasServices;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatuses;
use Liquetsoft\Fias\Component\Tests\HttpTransportCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который проверяет статус сервисов ФИАС.
 *
 * @internal
 */
class BaseFiasStatusCheckerTest extends HttpTransportCase
{
    /**
     * Проверяет, что объект возвращает правильный статус, если оба сервиса доступны.
     */
    public function testCheck(): void
    {
        $versionUrl = 'https://test.test/version';

        /** @var MockObject&FiasInformerResponse */
        $informerResult = $this->getMockBuilder(FiasInformerResponse::class)->getMock();
        $informerResult->method('getFullUrl')->willReturn($versionUrl);

        /** @var MockObject&FiasInformer */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->expects($this->exactly(2))
            ->method('getLatestVersion')
            ->willReturn($informerResult);

        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('head')
            ->with($this->equalTo($versionUrl))
            ->willReturn($this->createOkResponseMock());

        $checker = new BaseFiasStatusChecker($transport, $informer);
        $checkResult = $checker->check();
        $servicesStatuses = $checkResult->getPerServiceStatuses();

        $this->assertSame(FiasStatuses::AVAILABLE, $checkResult->getResultStatus());
        $this->assertCount(2, $servicesStatuses);
        $this->assertSame(FiasStatuses::AVAILABLE, $servicesStatuses[0]->getStatus());
        $this->assertSame(FiasServices::INFORMER, $servicesStatuses[0]->getService());
        $this->assertSame(FiasStatuses::AVAILABLE, $servicesStatuses[1]->getStatus());
        $this->assertSame(FiasServices::FILE_SERVER, $servicesStatuses[1]->getService());
    }

    /**
     * Проверяет, что объект возвращает правильный статус, если сервис информирования недоступен.
     */
    public function testCheckInformerUnavailable(): void
    {
        /** @var MockObject&FiasInformer */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->method('getLatestVersion')->willThrowException(new \RuntimeException());

        $transport = $this->createTransportMock();
        $transport->expects($this->never())->method('head');

        $checker = new BaseFiasStatusChecker($transport, $informer);
        $checkResult = $checker->check();
        $servicesStatuses = $checkResult->getPerServiceStatuses();

        $this->assertSame(FiasStatuses::NOT_AVAILABLE, $checkResult->getResultStatus());
        $this->assertCount(2, $servicesStatuses);
        $this->assertSame(FiasStatuses::NOT_AVAILABLE, $servicesStatuses[0]->getStatus());
        $this->assertSame(FiasServices::INFORMER, $servicesStatuses[0]->getService());
        $this->assertSame(FiasStatuses::UNKNOWN, $servicesStatuses[1]->getStatus());
        $this->assertSame(FiasServices::FILE_SERVER, $servicesStatuses[1]->getService());
    }

    /**
     * Проверяет, что объект возвращает правильный статус, если файловый сервер недоступен.
     */
    public function testCheckFileServerUnavailable(): void
    {
        $versionUrl = 'https://test.test/version';

        /** @var MockObject&FiasInformerResponse */
        $informerResult = $this->getMockBuilder(FiasInformerResponse::class)->getMock();
        $informerResult->method('getFullUrl')->willReturn($versionUrl);

        /** @var MockObject&FiasInformer */
        $informer = $this->getMockBuilder(FiasInformer::class)->getMock();
        $informer->expects($this->exactly(2))
            ->method('getLatestVersion')
            ->willReturn($informerResult);

        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('head')
            ->with($this->equalTo($versionUrl))
            ->willReturn($this->createBadResponseMock());

        $checker = new BaseFiasStatusChecker($transport, $informer);
        $checkResult = $checker->check();
        $servicesStatuses = $checkResult->getPerServiceStatuses();

        $this->assertSame(FiasStatuses::NOT_AVAILABLE, $checkResult->getResultStatus());
        $this->assertCount(2, $servicesStatuses);
        $this->assertSame(FiasStatuses::AVAILABLE, $servicesStatuses[0]->getStatus());
        $this->assertSame(FiasServices::INFORMER, $servicesStatuses[0]->getService());
        $this->assertSame(FiasStatuses::NOT_AVAILABLE, $servicesStatuses[1]->getStatus());
        $this->assertSame(FiasServices::FILE_SERVER, $servicesStatuses[1]->getService());
    }
}
