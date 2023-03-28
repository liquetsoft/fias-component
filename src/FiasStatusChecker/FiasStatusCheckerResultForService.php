<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Интерфейс для объекта, который содержит информацию о проверке статуса одного сервиса.
 */
interface FiasStatusCheckerResultForService
{
    /**
     * Возвращает статус состояния сервиса.
     */
    public function getStatus(): FiasStatusCheckerStatus;

    /**
     * Возвращает тип сервиса.
     */
    public function getService(): FiasStatusCheckerService;

    /**
     * Возвращает причину установки статуса.
     */
    public function getReason(): string;
}
