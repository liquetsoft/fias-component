<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use SoapClient;
use SoapFault;

/**
 * Объект, который получает ссылку на файл с архивом ФИАС
 * от soap сервиса информирования ФИАС.
 */
class SoapFiasInformer implements FiasInformer
{
    /**
     * @var string
     */
    private $wsdl = '';

    /**
     * @var SoapClient|null
     */
    private $soapClient;

    /**
     * @param SoapClient|string $soapClient
     */
    public function __construct($soapClient = 'http://fias.nalog.ru/WebServices/Public/DownloadService.asmx?WSDL')
    {
        if ($soapClient instanceof SoapClient) {
            $this->soapClient = $soapClient;
        } else {
            $this->wsdl = $soapClient;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws SoapFault
     */
    public function getCompleteInfo(): InformerResponse
    {
        $response = $this->getSoapClient()->__call(
            'GetLastDownloadFileInfo',
            []
        );

        $versionId = $response->GetLastDownloadFileInfoResult->VersionId ?? 0;
        $url = $response->GetLastDownloadFileInfoResult->FiasCompleteXmlUrl ?? '';

        return $this->createResponseObject(
            (int) $versionId,
            (string) $url
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws SoapFault
     */
    public function getDeltaInfo(int $currentVersion): InformerResponse
    {
        $versions = $this->getDeltaList();

        $delta = new InformerResponseBase();
        foreach ($versions as $version) {
            $versionNumber = $version->getVersion();
            if ($versionNumber > $currentVersion && (!$delta->hasResult() || $delta->getVersion() > $versionNumber)) {
                $delta = $version;
            }
        }

        return $delta;
    }

    /**
     * {@inheritDoc}
     *
     * @throws SoapFault
     */
    public function getDeltaList(): array
    {
        $response = $this->getSoapClient()->__call(
            'GetAllDownloadFileInfo',
            []
        );
        $response = $response->GetAllDownloadFileInfoResult->DownloadFileInfo ?? [];

        $list = [];
        foreach ($response as $responseObject) {
            $versionId = $responseObject->VersionId ?? 0;
            $url = $responseObject->FiasDeltaXmlUrl ?? '';
            $list[] = $this->createResponseObject((int) $versionId, (string) $url);
        }

        return $list;
    }

    /**
     * Создает объект с ответом по номеру версии и url.
     *
     * @param int    $versionId
     * @param string $url
     *
     * @return InformerResponse
     */
    private function createResponseObject(int $versionId, string $url): InformerResponse
    {
        $res = new InformerResponseBase();
        $res->setVersion($versionId);
        $res->setUrl($url);

        return $res;
    }

    /**
     * Возвращает объект SOAP-клиента для запросов.
     *
     * @return SoapClient
     *
     * @throws SoapFault
     */
    private function getSoapClient(): SoapClient
    {
        if ($this->soapClient === null) {
            $this->soapClient = new SoapClient(
                $this->wsdl,
                [
                    'exceptions' => true,
                ]
            );
        }

        return $this->soapClient;
    }
}
