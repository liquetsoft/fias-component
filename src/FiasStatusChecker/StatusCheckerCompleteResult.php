<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Объект, который содержит информацию о проверке статуса.
 */
final class StatusCheckerCompleteResult implements StatusCheckerResult
{
    private readonly FiasStatuses $resultStatus;

    /**
     * @var StatusCheckerServiceResult[]
     */
    private readonly array $perServiceStatuses;

    /**
     * @param StatusCheckerServiceResult[] $perServiceStatuses
     */
    public function __construct(FiasStatuses $resultStatus, array $perServiceStatuses)
    {
        $this->resultStatus = $resultStatus;
        $this->perServiceStatuses = $perServiceStatuses;
    }

    public function getResultStatus(): FiasStatuses
    {
        return $this->resultStatus;
    }

    public function getPerServiceStatuses(): array
    {
        return $this->perServiceStatuses;
    }
}
