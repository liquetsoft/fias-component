<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFile;

/**
 * Интерфейс для объекта, который представляет файл.
 */
interface FiasFile extends \Stringable
{
    /**
     * Возвращает размер заархивированного объекта.
     */
    public function getSize(): int;

    /**
     * Возвращает имя заархивированного объекта.
     */
    public function getName(): string;
}
