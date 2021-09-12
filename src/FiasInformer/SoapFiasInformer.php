<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;
use SoapClient;
use SoapFault;
use Throwable;

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
        try {
            $response = $this->getSoapClient()->__call(
                'GetLastDownloadFileInfo',
                []
            );
        } catch (Throwable $e) {
            throw new FiasInformerException($e->getMessage(), 0, $e);
        }

        $versionId = $response->GetLastDownloadFileInfoResult->VersionId ?? 0;
        $url = $response->GetLastDownloadFileInfoResult->GarXMLFullURL ?? '';

        if ($versionId === 0) {
            $message = "Informer can't find complete version in SOAP response.";
            throw new FiasInformerException($message);
        } elseif ($url === '') {
            // иногда версия появляется без ссылки на xml архив
            $message = "There is no xml archive set for {$versionId} complete version.";
            throw new FiasInformerException($message);
        }

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
        try {
            $response = $this->getSoapClient()->__call(
                'GetAllDownloadFileInfo',
                []
            );
        } catch (Throwable $e) {
            throw new FiasInformerException($e->getMessage(), 0, $e);
        }

        $response = $response->GetAllDownloadFileInfoResult->DownloadFileInfo ?? [];

        $list = [];
        foreach ($response as $responseObject) {
            $versionId = $responseObject->VersionId ?? 0;
            $url = $responseObject->GarXMLDeltaURL ?? '';
            if ($url !== '') {
                // похоже только так это работает, дельта появляется не сразу
                // поэтому просто пропускаем объекты без дельты
                $list[] = $this->createResponseObject((int) $versionId, (string) $url);
            }
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
