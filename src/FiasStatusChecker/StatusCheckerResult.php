<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Объект, который содержит информацию о проверке статуса.
 */
class StatusCheckerResult
{
    /**
     * @var string
     */
    private $resultStatus;

    /**
     * @var array<int, array>
     */
    private $perServiceStatuses;

    /**
     * @param string            $resultStatus
     * @param array<int, array> $perServiceStatuses
     */
    public function __construct(string $resultStatus, array $perServiceStatuses)
    {
        $this->resultStatus = $resultStatus;
        $this->perServiceStatuses = $perServiceStatuses;
    }

    /**
     * Возвращает статус общего сосотояния ФИАС.
     *
     * @return string
     */
    public function getResultStatus(): string
    {
        return $this->resultStatus;
    }

    /**
     * Возвращает массив со статусами по каждому сервису.
     *
     * @return array<int, array>
     */
    public function getPerServiceStatuses(): array
    {
        return $this->perServiceStatuses;
    }
}
