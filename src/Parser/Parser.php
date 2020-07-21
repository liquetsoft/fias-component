<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parser;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use SplFileInfo;

/**
 * Интерфейс парсинга файла xml/dbf.
 */
interface Parser
{
    /**
     * Получить сущности
     *
     * @param SplFileInfo $file
     * @param EntityDescriptor $descriptor
     * @param string $entity_class
     *
     * @return \Generator
     */
    public function getEntities(SplFileInfo $file, EntityDescriptor $descriptor, string $entityСlass): \Generator;
}
