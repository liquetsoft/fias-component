<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use InvalidArgumentException;

/**
 * Интерфейс для объекта, который предоставляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 */
interface InformerResponse
{
    /**
     * Задает версию ФИАС, для которой получена ссылка.
     *
     * @param int $version
     *
     * @return InformerResponse
     */
    public function setVersion(int $version): InformerResponse;

    /**
     * Возвращает версию ФИАС, для которой получена ссылка.
     *
     * @return int
     */
    public function getVersion(): int;

    /**
     * Проверяет корректность ссылки.
     *
     * @param string $url
     *
     * @return string|false
     */
    public function validateUrl(string $url);

    /**
     * Задает ссылку, по которой можно скачать файл.
     *
     * @param string $url
     *
     * @return InformerResponse
     *
     * @throws InvalidArgumentException
     */
    public function setUrl(string $url): InformerResponse;

    /**
     * Получает ссылку, по которой можно скачать файл.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Проверяет содержит ли данный объект ответ от сервиса или нет.
     *
     * @return bool
     */
    public function hasResult(): bool;
}
