<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Объект, который содержит информацию о проверке статуса.
 */
final class FiasStatusCheckerResultImpl implements FiasStatusCheckerResult
{
    /**
     * @param FiasStatusCheckerResultForService[] $perServiceStatuses
     */
    public function __construct(
        private readonly FiasStatusCheckerStatus $resultStatus,
        private readonly array $perServiceStatuses,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getResultStatus(): FiasStatusCheckerStatus
    {
        return $this->resultStatus;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getPerServiceStatuses(): array
    {
        return $this->perServiceStatuses;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function canProceed(): bool
    {
        return $this->resultStatus === FiasStatusCheckerStatus::AVAILABLE;
    }
}
