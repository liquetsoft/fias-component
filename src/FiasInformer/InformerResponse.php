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
     * Задает версию ФИАС, для которой получена ссылка.
     */
    public function setVersion(int $version): InformerResponse;

    /**
     * Возвращает версию ФИАС, для которой получена ссылка.
     */
    public function getVersion(): int;

    /**
     * Задает ссылку, по которой можно скачать файл.
     *
     * @throws \InvalidArgumentException
     */
    public function setUrl(string $url): InformerResponse;

    /**
     * Получает ссылку, по которой можно скачать файл.
     */
    public function getUrl(): string;

    /**
     * Проверяет содержит ли данный объект ответ от сервиса или нет.
     */
    public function hasResult(): bool;
}
