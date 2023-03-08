<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasStatusChecker;

/**
 * Список сервисов ФИАС для проверки состояния.
 */
enum FiasServices: string
{
    case INFORMER = 'informer';
    case FILE_SERVER = 'file server';
}
