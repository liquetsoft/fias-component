<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Сервис, который проверяет доступность всех компонентов ФИАС (сервиса информирования, файла для загрузки и т.д.).
 */
interface FiasStatusChecker
{
    /**
     * Проверяет статусы всех компонентов ФИАС.
     */
    public function check(): StatusCheckerResult;
}
