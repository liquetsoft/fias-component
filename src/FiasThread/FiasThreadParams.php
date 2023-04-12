<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasThread;

/**
 * Интерфейс для объекта, который содержит описание параметров для запуска трэда.
 */
interface FiasThreadParams
{
    /**
     * Возвращает ассоциативный массив с параметрами для запуска трэда.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}
