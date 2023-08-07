<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

use Liquetsoft\Fias\Component\Exception\HttpTransportException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;

/**
 * Объект, который проверяет статус сервисов ФИАС.
 */
final class FiasStatusCheckerImpl implements FiasStatusChecker
{
    public function __construct(
        private readonly HttpTransport $transport,
        private readonly FiasInformer $informer
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function check(): FiasStatusCheckerResult
    {
        $statusesPerServices = [
            $this->getFiasInformerStatus(),
            $this->getFileServerStatus(),
        ];

        foreach ($statusesPerServices as $status) {
            if ($status->getStatus() !== FiasStatusCheckerStatus::AVAILABLE) {
                return new FiasStatusCheckerResultImpl(FiasStatusCheckerStatus::NOT_AVAILABLE, $statusesPerServices);
            }
        }

        return new FiasStatusCheckerResultImpl(FiasStatusCheckerStatus::AVAILABLE, $statusesPerServices);
    }

    /**
     * Возвращает состояние сервиса информирования.
     */
    private function getFiasInformerStatus(): FiasStatusCheckerResultForService
    {
        $status = FiasStatusCheckerStatus::AVAILABLE;
        $service = FiasStatusCheckerService::INFORMER;
        $reason = '';

        try {
            $this->informer->getLatestVersion();
        } catch (\Throwable $e) {
            $status = FiasStatusCheckerStatus::NOT_AVAILABLE;
            $reason = $e->getMessage();
        }

        return new FiasStatusCheckerResultForServiceImpl($status, $service, $reason);
    }

    /**
     * Возвращает состояние файлового сервера.
     */
    private function getFileServerStatus(): FiasStatusCheckerResultForService
    {
        $service = FiasStatusCheckerService::FILE_SERVER;

        try {
            $url = $this->informer->getLatestVersion()->getFullUrl();
        } catch (\Throwable $e) {
            return new FiasStatusCheckerResultForServiceImpl(
                FiasStatusCheckerStatus::UNKNOWN,
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
            return new FiasStatusCheckerResultForServiceImpl(
                FiasStatusCheckerStatus::NOT_AVAILABLE,
                $service,
                $e->getMessage()
            );
        }

        return new FiasStatusCheckerResultForServiceImpl(FiasStatusCheckerStatus::AVAILABLE, $service);
    }
}
