<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Интерфейс для объекта, который содержит информацию о проверке статуса.
 */
interface StatusCheckerResult
{
    /**
     * Возвращает статус общего сосотояния ФИАС.
     */
    public function getResultStatus(): FiasStatuses;

    /**
     * Возвращает массив со статусами по каждому сервису.
     *
     * @return StatusCheckerServiceResult[]
     */
    public function getPerServiceStatuses(): array;
}
