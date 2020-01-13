<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use SoapClient;

/**
 * Объект, который получает ссылку на файл с архивом ФИАС
 * от soap сервиса информирования ФИАС.
 */
class SoapFiasInformer implements FiasInformer
{
    /**
     * @var string
     */
    protected $wsdl = '';

    /**
     * @var SoapClient|null
     */
    protected $soapClient;

    /**
     * @param SoapClient|string $soapClient
     */
    public function __construct($soapClient)
    {
        if ($soapClient instanceof SoapClient) {
            $this->soapClient = $soapClient;
        } else {
            $this->wsdl = $soapClient;
        }
    }

    /**
     * @inheritdoc
     */
    public function getCompleteInfo(): InformerResponse
    {
        $response = $this->getSoapClient()->__call('GetLastDownloadFileInfo', []);

        $res = new InformerResponseBase;
        $res->setVersion((int) $response->GetLastDownloadFileInfoResult->VersionId);
        $res->setUrl($response->GetLastDownloadFileInfoResult->FiasCompleteXmlUrl);

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function getDeltaInfo(int $version): InformerResponse
    {
        $response = $this->getSoapClient()->__call('GetAllDownloadFileInfo', []);
        $versions = $this->sortResponseByVersion($response->GetAllDownloadFileInfoResult->DownloadFileInfo);

        $res = new InformerResponseBase;
        foreach ($versions as $serviceVersion) {
            if ((int) $serviceVersion['VersionId'] <= $version) {
                continue;
            }
            $res->setVersion((int) $serviceVersion['VersionId']);
            $res->setUrl($serviceVersion['FiasDeltaXmlUrl']);
            break;
        }

        return $res;
    }

    /**
     * Сортирует ответ по номерам версии по возрастанию.
     *
     * @param array $response
     *
     * @return array
     */
    protected function sortResponseByVersion(array $response): array
    {
        $versions = [];
        $versionsSort = [];
        foreach ($response as $key => $version) {
            $versions[$key] = (array) $version;
            $versionsSort[$key] = (int) $version->VersionId;
        }
        array_multisort($versionsSort, SORT_ASC, $versions);

        return $versions;
    }

    /**
     * Возвращает объект SOAP-клиента для запросов.
     *
     * @return SoapClient
     */
    protected function getSoapClient(): SoapClient
    {
        if ($this->soapClient === null) {
            $this->soapClient = new SoapClient($this->wsdl, [
                'exceptions' => true,
            ]);
        }

        return $this->soapClient;
    }
}
