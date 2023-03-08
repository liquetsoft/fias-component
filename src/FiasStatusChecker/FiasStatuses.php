<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Список состояний, которые может определить для сервисов ФИАС проверка состояния.
 */
enum FiasStatuses: string
{
    case AVAILABLE = 'available';
    case NOT_AVAILABLE = 'not available';
    case UNKNOWN = 'unknown';
}
