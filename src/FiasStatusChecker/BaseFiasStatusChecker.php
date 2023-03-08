<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

use Liquetsoft\Fias\Component\Exception\HttpTransportException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;

/**
 * Объект, который проверяет статус сервисов ФИАС.
 */
final class BaseFiasStatusChecker implements FiasStatusChecker
{
    private readonly HttpTransport $transport;

    private readonly FiasInformer $informer;

    public function __construct(HttpTransport $transport, FiasInformer $informer)
    {
        $this->transport = $transport;
        $this->informer = $informer;
    }

    /**
     * {@inheritDoc}
     */
    public function check(): StatusCheckerResult
    {
        $statusesPerServices = [
            $this->getFiasInformerStatus(),
            $this->getFileServerStatus(),
        ];

        foreach ($statusesPerServices as $status) {
            if ($status->getStatus() !== FiasStatuses::AVAILABLE) {
                return new StatusCheckerCompleteResult(FiasStatuses::NOT_AVAILABLE, $statusesPerServices);
            }
        }

        return new StatusCheckerCompleteResult(FiasStatuses::AVAILABLE, $statusesPerServices);
    }

    /**
     * Возвращает состояние сервиса информирования.
     */
    private function getFiasInformerStatus(): StatusCheckerServiceResult
    {
        $status = FiasStatuses::AVAILABLE;
        $service = FiasServices::INFORMER;
        $reason = '';

        try {
            $this->informer->getLatestVersion();
        } catch (\Throwable $e) {
            $status = FiasStatuses::NOT_AVAILABLE;
            $reason = $e->getMessage();
        }

        return new StatusCheckerServiceResult($status, $service, $reason);
    }

    /**
     * Возвращает состояние файлового сервера.
     */
    private function getFileServerStatus(): StatusCheckerServiceResult
    {
        $service = FiasServices::FILE_SERVER;

        try {
            $url = $this->informer->getLatestVersion()->getFullUrl();
        } catch (\Throwable $e) {
            return new StatusCheckerServiceResult(
                FiasStatuses::UNKNOWN,
                $service,
                'Informer is unavailable'
            );
        }

        try {
            $response = $this->transport->head($url);
            if (!$response->isOk()) {
                throw HttpTransportException::create(
                    "Can't reach file '%s', bad status '%s'",
                    $url,
                    $response->getStatusCode()
                );
            }
        } catch (\Throwable $e) {
            return new StatusCheckerServiceResult(
                FiasStatuses::NOT_AVAILABLE,
                $service,
                $e->getMessage()
            );
        }

        return new StatusCheckerServiceResult(FiasStatuses::AVAILABLE, $service);
    }
}
