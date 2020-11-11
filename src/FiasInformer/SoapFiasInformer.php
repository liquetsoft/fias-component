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
                $typeFormat = 'FiasCompleteXmlUrl';
                break;
            case 'dbf':
                $typeFormat = 'FiasCompleteDbfUrl';
                break;
            default:
                throw new InvalidArgumentException("Unsupported type: \"{$type}\"");
        }
        $res->setVersion((int) $response->GetLastDownloadFileInfoResult->VersionId);

        $url = $response->GetLastDownloadFileInfoResult->$typeFormat;
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
                $typeFormat = 'FiasDeltaXmlUrl';
                break;
            case 'dbf':
                $typeFormat = 'FiasDeltaDbfUrl';
                break;
            default:
                throw new InvalidArgumentException("Unsupported type: \"{$type}\"");
        }
        foreach ($versions as $serviceVersion) {
            $url = $serviceVersion[$typeFormat];
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
     * @param array      $args
     * @param int        $interval
     * @param int        $maxAttempts
     *
     * @return mixed
     */
    protected function makeSoapRequestWithRetry(
        array $args,
        $interval = 1,
        $maxAttempts = 3
    ) {
        $previous = null;
        for ($attempts = 0; $attempts < $maxAttempts; $attempts++) {
            try {
                return call_user_func_array([$this->getSoapClient(), '__call'], $args);
            } catch (\Throwable $th) {
                $previous = $th;
            }
            sleep($interval);
        }
        throw new \RuntimeException(
            "Could not connect to host SoapFiasInformer {$maxAttempts} times",
            1,
            $previous
        );
    }
}
