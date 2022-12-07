<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;
use Liquetsoft\Fias\Component\FiasInformer\SoapFiasInformer;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который получает ссылку на файл с архивом ФИАС
 * от soap сервиса информирования ФИАС.
 *
 * @internal
 */
class SoapFiasInformerTest extends BaseCase
{
    /**
     * Проверяет, что сервис информирования возвращает ссылку на полный файл ФИАС.
     *
     * @throws \SoapFault
     */
    public function testGetCompleteInfo(): void
    {
        $soapResponse = new \stdClass();
        $soapResponse->GetLastDownloadFileInfoResult = new \stdClass();
        $soapResponse->GetLastDownloadFileInfoResult->GarXMLFullURL = $this->createFakeData()->url();
        $soapResponse->GetLastDownloadFileInfoResult->VersionId = $this->createFakeData()->randomNumber();

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->method('__call')
            ->with(
                $this->identicalTo('GetLastDownloadFileInfo')
            )
            ->willReturn($soapResponse);

        $service = new SoapFiasInformer($soapClient);
        $result = $service->getCompleteInfo();

        $this->assertSame(
            $soapResponse->GetLastDownloadFileInfoResult->GarXMLFullURL,
            $result->getUrl()
        );
        $this->assertSame(
            $soapResponse->GetLastDownloadFileInfoResult->VersionId,
            $result->getVersion()
        );
    }

    /**
     * Проверяет, что сервис информирования перхватит исключение от SOAP.
     *
     * @throws \SoapFault
     */
    public function testGetCompleteInfoSoapException(): void
    {
        $soapResponse = new \stdClass();
        $soapResponse->GetLastDownloadFileInfoResult = new \stdClass();
        $soapResponse->GetLastDownloadFileInfoResult->GarXMLFullURL = $this->createFakeData()->url();

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->method('__call')
            ->with(
                $this->identicalTo('GetLastDownloadFileInfo')
            )
            ->will(
                $this->throwException(new \RuntimeException())
            );

        $service = new SoapFiasInformer($soapClient);

        $this->expectException(FiasInformerException::class);
        $service->getCompleteInfo();
    }

    /**
     * Проверяет, что сервис информирования выбросит ошибку, если не найдет полную версию.
     *
     * @throws \SoapFault
     */
    public function testGetCompleteInfoNoVersionException(): void
    {
        $soapResponse = new \stdClass();
        $soapResponse->GetLastDownloadFileInfoResult = new \stdClass();
        $soapResponse->GetLastDownloadFileInfoResult->GarXMLFullURL = $this->createFakeData()->url();

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->method('__call')
            ->with(
                $this->identicalTo('GetLastDownloadFileInfo')
            )
            ->willReturn($soapResponse);

        $service = new SoapFiasInformer($soapClient);

        $this->expectException(FiasInformerException::class);
        $service->getCompleteInfo();
    }

    /**
     * Проверяет, что сервис информирования выбросит ошибку, если не найдет ссылку
     * на полную версию.
     *
     * @throws \SoapFault
     */
    public function testGetCompleteInfoNoUrlException(): void
    {
        $soapResponse = new \stdClass();
        $soapResponse->GetLastDownloadFileInfoResult = new \stdClass();
        $soapResponse->GetLastDownloadFileInfoResult->VersionId = $this->createFakeData()->randomNumber();

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->method('__call')
            ->with(
                $this->identicalTo('GetLastDownloadFileInfo')
            )
            ->willReturn($soapResponse);

        $service = new SoapFiasInformer($soapClient);

        $this->expectException(FiasInformerException::class);
        $service->getCompleteInfo();
    }

    /**
     * Проверяет, что сервис информирования возвращает ссылку на дельту для указанной версии.
     *
     * @throws \SoapFault
     */
    public function testGetDeltaInfo(): void
    {
        $soapResponse = new \stdClass();
        $soapResponse->GetAllDownloadFileInfoResult = new \stdClass();
        $soapResponse->GetAllDownloadFileInfoResult->DownloadFileInfo = [];

        $totalDeltas = 10;
        $currentDelta = $this->createFakeData()->numberBetween(1, $totalDeltas - 1);
        $nextDelta = $currentDelta + 1;
        $nextUrl = null;
        for ($i = 1; $i <= $totalDeltas; ++$i) {
            $delta = new \stdClass();
            $delta->VersionId = $i;
            $delta->GarXMLDeltaURL = $this->createFakeData()->url();
            $soapResponse->GetAllDownloadFileInfoResult->DownloadFileInfo[] = $delta;
            if ($i === $nextDelta) {
                $nextUrl = $delta->GarXMLDeltaURL;
            }
        }
        shuffle($soapResponse->GetAllDownloadFileInfoResult->DownloadFileInfo);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->method('__call')
            ->with(
                $this->identicalTo('GetAllDownloadFileInfo')
            )
            ->willReturn($soapResponse);

        $service = new SoapFiasInformer($soapClient);
        $result = $service->getDeltaInfo($currentDelta);

        $this->assertSame($nextUrl, $result->getUrl());
        $this->assertSame($nextDelta, $result->getVersion());
    }

    /**
     * Проверяет, что сервис информирования перехватит исключение от SOAP.
     *
     * @throws \SoapFault
     */
    public function testGetDeltaInfoException(): void
    {
        $soapResponse = new \stdClass();
        $soapResponse->GetAllDownloadFileInfoResult = new \stdClass();
        $soapResponse->GetAllDownloadFileInfoResult->DownloadFileInfo = [];

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->method('__call')
            ->with(
                $this->identicalTo('GetAllDownloadFileInfo')
            )
            ->will(
                $this->throwException(new \RuntimeException())
            );

        $service = new SoapFiasInformer($soapClient);

        $this->expectException(FiasInformerException::class);
        $service->getDeltaInfo(100);
    }
}
