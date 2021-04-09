<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

use Liquetsoft\Fias\Component\Exception\StatusCheckerException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Throwable;

/**
 * Объект, который проверяет статус сервисов ФИАС с помощью запросов через curl.
 */
class CurlStatusChecker implements FiasStatusChecker
{
    /**
     * @var string
     */
    private $wsdlUrl;

    /**
     * @var FiasInformer
     */
    private $informer;

    public function __construct(string $wsdlUrl, FiasInformer $informer)
    {
        $this->wsdlUrl = $wsdlUrl;
        $this->informer = $informer;
    }

    /**
     * {@inheritDoc}
     */
    public function check(): StatusCheckerResult
    {
        if (!$this->sendHeadRequest($this->wsdlUrl)) {
            return new StatusCheckerResult(
                FiasStatusChecker::STATUS_NOT_AVAILABLE,
                [
                    [
                        'service' => FiasStatusChecker::SERVICE_INFORMER,
                        'status' => FiasStatusChecker::STATUS_NOT_AVAILABLE,
                        'reason' => 'WSDL file is unavailable',
                    ],
                    [
                        'service' => FiasStatusChecker::SERVICE_FILE_SERVER,
                        'status' => FiasStatusChecker::STATUS_UNKNOWN,
                        'reason' => 'Informer is unavailable',
                    ],
                ]
            );
        }

        try {
            $info = $this->informer->getCompleteInfo();
        } catch (Throwable $e) {
            return new StatusCheckerResult(
                FiasStatusChecker::STATUS_NOT_AVAILABLE,
                [
                    [
                        'service' => FiasStatusChecker::SERVICE_INFORMER,
                        'status' => FiasStatusChecker::STATUS_NOT_AVAILABLE,
                        'reason' => $e->getMessage(),
                    ],
                    [
                        'service' => FiasStatusChecker::SERVICE_FILE_SERVER,
                        'status' => FiasStatusChecker::STATUS_UNKNOWN,
                        'reason' => 'Informer is unavailable',
                    ],
                ]
            );
        }

        if (!$this->sendHeadRequest($info->getUrl())) {
            return new StatusCheckerResult(
                FiasStatusChecker::STATUS_NOT_AVAILABLE,
                [
                    [
                        'service' => FiasStatusChecker::SERVICE_INFORMER,
                        'status' => FiasStatusChecker::STATUS_AVAILABLE,
                        'reason' => '',
                    ],
                    [
                        'service' => FiasStatusChecker::SERVICE_FILE_SERVER,
                        'status' => FiasStatusChecker::STATUS_NOT_AVAILABLE,
                        'reason' => 'File to download in unaviable',
                    ],
                ]
            );
        }

        return new StatusCheckerResult(
            FiasStatusChecker::STATUS_AVAILABLE,
            [
                [
                    'service' => FiasStatusChecker::SERVICE_INFORMER,
                    'status' => FiasStatusChecker::STATUS_AVAILABLE,
                    'reason' => '',
                ],
                [
                    'service' => FiasStatusChecker::SERVICE_FILE_SERVER,
                    'status' => FiasStatusChecker::STATUS_AVAILABLE,
                    'reason' => '',
                ],
            ]
        );
    }

    /**
     * Отправляет HEAD запрос на указанный url и проверяет, что в ответ пришел статус 200.
     *
     * @param string $url
     *
     * @return bool
     */
    private function sendHeadRequest(string $url): bool
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new StatusCheckerException("Can't init curl resource.");
        }

        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $url,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_NOBODY => true,
            ]
        );

        $res = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return empty($error) && $httpCode === 200;
    }
}
