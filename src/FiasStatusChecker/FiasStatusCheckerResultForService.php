<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Объект, который содержит информацию о проверке статуса одного сервиса.
 */
final class FiasStatusCheckerResultForService
{
    private readonly FiasStatusCheckerStatus $status;

    private readonly FiasStatusCheckerService $service;

    private readonly string $reason;

    public function __construct(FiasStatusCheckerStatus $status, FiasStatusCheckerService $service, string $reason = '')
    {
        $this->status = $status;
        $this->service = $service;
        $this->reason = $reason;
    }

    /**
     * Возвращает статус состояния сервиса.
     */
    public function getStatus(): FiasStatusCheckerStatus
    {
        return $this->status;
    }

    /**
     * Возвращает тип сервиса.
     */
    public function getService(): FiasStatusCheckerService
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
