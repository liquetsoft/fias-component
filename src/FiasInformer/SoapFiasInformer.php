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
        $response = $this->makeSoapRequestWithRetry(['GetLastDownloadFileInfo', []]);
        $res = new InformerResponseBase;

        switch ($type) {
            case 'xml':
                $type_format = 'FiasCompleteXmlUrl';
                break;
            case 'dbf':
                $type_format = 'FiasCompleteDbfUrl';
                break;
            default:
                throw new InvalidArgumentException("Unsupported type: \"{$type}\"");
        }
        $res->setVersion((int) $response->GetLastDownloadFileInfoResult->VersionId);

        $url = $response->GetLastDownloadFileInfoResult->$type_format;
        $res->setUrl($url);

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function getDeltaInfo(int $version, string $type): InformerResponse
    {
        $response = $this->makeSoapRequestWithRetry(['GetAllDownloadFileInfo', []]);
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
                throw new InvalidArgumentException("Unsupported type: \"{$type}\"");
        }
        foreach ($versions as $serviceVersion) {
            $url = $serviceVersion[$type_format];
            if ((int) $serviceVersion['VersionId'] <= $version) {
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

    /**
     * Осуществляет несколько попыток подключения Soap компонента
     *
     * @param SoapClient $soap_client
     * @param array      $args
     * @param int        $interval
     * @param int        $max_attempts
     *
     * @return mixed
     * @throws \RuntimeExeption
     */
    protected function makeSoapRequestWithRetry(
        array $args,
        $interval = 1,
        $max_attempts = 3
    ) {
        $reflectionMethod = new \ReflectionMethod('SoapClient', '__call');
        
        for ($attempts = 0; $attempts < $max_attempts; $attempts++) {
            try {
                return $reflectionMethod->invokeArgs($this->getSoapClient(), $args);
            } catch (\Throwable $th) {
                $previous = $th;
            }
            sleep($interval);
        }
        throw new \RuntimeException(
            "Could not connect to host SoapFiasInformer {$max_attempts} times",
            1,
            $previous
        );
    }
}
