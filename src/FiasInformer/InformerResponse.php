<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

/**
 * Интерфейс для объекта, который предоставляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 */
interface InformerResponse
{
    /**
     * Возвращает версию ФИАС, для которой получена ссылка.
     */
    public function getVersion(): int;

    /**
     * Получает ссылку, по которой можно скачать файл.
     */
    public function getUrl(): string;
}
