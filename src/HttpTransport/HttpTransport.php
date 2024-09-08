<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\HttpTransport;

use Liquetsoft\Fias\Component\Exception\HttpTransportException;

/**
 * Интерфейс для объекта, который может отправлять http запросы.
 */
interface HttpTransport
{
    /**
     * Отправляет HEAD запрос, чтобы получить информацию о сервере.
     *
     * @throws HttpTransportException
     */
    public function head(string $url): HttpTransportResponse;

    /**
     * Отправляет GET запрос.
     *
     * @throws HttpTransportException
     */
    public function get(string $url, array $params = []): HttpTransportResponse;

    /**
     * Запускает скачивание файла по ссылке в указанный ресурс.
     *
     * @param resource $destination
     *
     * @throws HttpTransportException
     */
    public function download(string $url, $destination, ?int $bytesFrom = null, ?int $bytesTo = null): HttpTransportResponse;
}
