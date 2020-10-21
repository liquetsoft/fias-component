<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use SoapClient;
use InvalidArgumentException;

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
    public function getCompleteInfo(string $type): InformerResponse
    {
        $response = $this->getSoapClient()->__call('GetLastDownloadFileInfo', []);
        $res = new InformerResponseBase;

        switch ($type) {
            case 'xml':
                $type_format = 'FiasCompleteXmlUrl';
                break;
            case 'dbf':
                $type_format = 'FiasCompleteDbfUrl';
                break;
            default:
                throw new InvalidArgumentException("Unsupported required type: \"{$type}\"");
        }
        $res->setVersion((int) $response->GetLastDownloadFileInfoResult->VersionId);

        $url = $response->GetLastDownloadFileInfoResult->$type_format;
        
        if ($res->validateUrl($url)) {
            $res->setUrl($url);
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function getDeltaInfo(int $version, string $type): InformerResponse
    {
        $response = $this->getSoapClient()->__call('GetAllDownloadFileInfo', []);
        $versions = $this->sortResponseByVersion($response->GetAllDownloadFileInfoResult->DownloadFileInfo);

        $res = new InformerResponseBase;

        switch ($type) {
            case 'xml':
                $type_format = 'FiasDeltaXmlUrl';
                break;
            case 'dbf':
                $type_format = 'FiasDeltaDbfUrl';
                break;
            default:
                throw new InvalidArgumentException("Unsupported required type: \"{$type}\"");
        }
        foreach ($versions as $serviceVersion) {
            $url = $serviceVersion[$type_format];
            if ((int) $serviceVersion['VersionId'] <= $version || !$res->validateUrl($url)) {
                continue;
            }
            $res->setVersion((int) $serviceVersion['VersionId']);
            $res->setUrl($url);
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
