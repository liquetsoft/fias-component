<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Объект, который содержит информацию о проверке статуса одного сервиса.
 */
final class FiasStatusCheckerResultForServiceImpl implements FiasStatusCheckerResultForService
{
    public function __construct(
        private readonly FiasStatusCheckerStatus $status,
        private readonly FiasStatusCheckerService $service,
        private readonly string $reason = '',
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus(): FiasStatusCheckerStatus
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function getService(): FiasStatusCheckerService
    {
        return $this->service;
    }

    /**
     * {@inheritDoc}
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
