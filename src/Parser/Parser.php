<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parser;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;

/**
 * Интерфейс парсинга файла xml/dbf.
 */
interface Parser
{
    /**
     * Получить сущности
     */
    public function getEntities(string $entity_class): \Generator;
}
