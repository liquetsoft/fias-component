<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Объект, который содержит информацию о проверке статуса одного сервиса.
 */
final class StatusCheckerServiceResult
{
    private readonly FiasStatuses $status;

    private readonly FiasServices $service;

    private readonly string $reason;

    public function __construct(FiasStatuses $status, FiasServices $service, string $reason = '')
    {
        $this->status = $status;
        $this->service = $service;
        $this->reason = $reason;
    }

    /**
     * Возвращает статус состояния сервиса.
     */
    public function getStatus(): FiasStatuses
    {
        return $this->status;
    }

    /**
     * Возвращает тип сервиса.
     */
    public function getService(): FiasServices
    {
        return $this->service;
    }

    /**
     * Возвращает причину установки статуса.
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
