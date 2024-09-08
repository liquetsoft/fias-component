<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Сервис, который проверяет доступность всех компонентов ФИАС (сервиса информирования, файла для загрузки и т.д.).
 */
interface FiasStatusChecker
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_NOT_AVAILABLE = 'not available';
    public const STATUS_UNKNOWN = 'unknown';

    public const SERVICE_INFORMER = 'informer';
    public const SERVICE_FILE_SERVER = 'file server';

    /**
     * Проверяет статусы всех компонентов ФИАС.
     */
    public function check(): StatusCheckerResult;
}
