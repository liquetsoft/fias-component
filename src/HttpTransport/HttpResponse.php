<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

/**
 * Интерфейс для объекта, содержащего http ответ.
 */
interface HttpResponse
{
    /**
     * Возвращает код ответа.
     */
    public function getStatusCode(): int;

    /**
     * Возвращает заголовки ответа.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array;

    /**
     * Возвращает правду, если ответ был успешным.
     */
    public function isOk(): bool;

    /**
     * Возвращает длину тела ответ.
     */
    public function getContentLength(): int;

    /**
     * Возвращает правду, если сервер поддерживает докачку файла.
     */
    public function isRangeSupported(): bool;

    /**
     * Возвращает тело ответа.
     */
    public function getPayload(): string;

    /**
     * Возвращает декодированное из json тело ответа.
     */
    public function getJsonPayload(): mixed;
}
