<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Интерфейс для объекта, который содержит информацию о проверке статуса.
 */
interface FiasStatusCheckerResult
{
    /**
     * Возвращает статус общего сосотояния ФИАС.
     */
    public function getResultStatus(): FiasStatusCheckerStatus;

    /**
     * Возвращает массив со статусами по каждому сервису.
     *
     * @return FiasStatusCheckerResultForService[]
     */
    public function getPerServiceStatuses(): array;

    /**
     * Возращает правду, если можно продолжать установку/обновление.
     */
    public function canProceed(): bool;
}
