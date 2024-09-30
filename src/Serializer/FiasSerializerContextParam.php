<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

/**
 * Список параметров контекста, которые поддерживает сериализатор.
 */
enum FiasSerializerContextParam: string
{
    case FIAS_FLAG = 'is_fias';
}
