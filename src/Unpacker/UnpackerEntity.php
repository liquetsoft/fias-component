<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

/**
 * Интерфейс для объекта, который представляет файл внутри архива.
 */
interface UnpackerEntity
{
    /**
     * Возвращает тип объекта.
     */
    public function getType(): UnpackerEntityType;

    /**
     * Возвращает индекс заархивированного объекта.
     */
    public function getIndex(): int;

    /**
     * Возвращает размер заархивированного объекта.
     */
    public function getSize(): int;

    /**
     * Возвращает имя заархивированного объекта.
     */
    public function getName(): string;
}
