<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\FiasInformer\SoapFiasInformer;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SoapClient;
use stdClass;

/**
 * Тест для объекта, который получает ссылку на файл с архивом ФИАС
 * от soap сервиса информирования ФИАС.
 */
class SoapFiasInformerTest extends BaseCase
{
    /**
     * Проверяет, что сервис информирования возвращает ссылку на полный файл ФИАС.
     */
    public function testGetCompleteInfo()
    {
        $soapResponse = new stdClass();
        $soapResponse->GetLastDownloadFileInfoResult = new stdClass();
        $soapResponse->GetLastDownloadFileInfoResult->FiasCompleteXmlUrl = $this->createFakeData()->url;
        $soapResponse->GetLastDownloadFileInfoResult->VersionId = $this->createFakeData()->randomNumber();

        $soapClient = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->method('__call')
            ->with($this->identicalTo('GetLastDownloadFileInfo'))
            ->will($this->returnValue($soapResponse));

        $service = new SoapFiasInformer($soapClient);
        $result = $service->getCompleteInfo();

        $this->assertSame(
            $soapResponse->GetLastDownloadFileInfoResult->FiasCompleteXmlUrl,
            $result->getUrl()
        );
        $this->assertSame(
            $soapResponse->GetLastDownloadFileInfoResult->VersionId,
            $result->getVersion()
        );
    }

    /**
     * Проверяет, что сервис информирования возвращает ссылку на дельту для указанной версии.
     */
    public function testGetDeltaInfo()
    {
        $soapResponse = new stdClass();
        $soapResponse->GetAllDownloadFileInfoResult = new stdClass();
        $soapResponse->GetAllDownloadFileInfoResult->DownloadFileInfo = [];

        $totalDeltas = 10;
        $currentDelta = $this->createFakeData()->numberBetween(1, $totalDeltas - 1);
        $nextDelta = $currentDelta + 1;
        $nextUrl = null;
        for ($i = 1; $i <= $totalDeltas; ++$i) {
            $delta = new stdClass();
            $delta->VersionId = $i;
            $delta->FiasDeltaXmlUrl = $this->createFakeData()->url;
            $soapResponse->GetAllDownloadFileInfoResult->DownloadFileInfo[] = $delta;
            if ($i === $nextDelta) {
                $nextUrl = $delta->FiasDeltaXmlUrl;
            }
        }
        shuffle($soapResponse->GetAllDownloadFileInfoResult->DownloadFileInfo);

        $soapClient = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient->method('__call')
            ->with($this->identicalTo('GetAllDownloadFileInfo'))
            ->will($this->returnValue($soapResponse));

        $service = new SoapFiasInformer($soapClient);
        $result = $service->getDeltaInfo($currentDelta);

        $this->assertSame($nextUrl, $result->getUrl());
        $this->assertSame($nextDelta, $result->getVersion());
    }
}
