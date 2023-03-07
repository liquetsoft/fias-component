<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;
use Liquetsoft\Fias\Component\Helper\FiasLinks;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;

/**
 * Объект, который получает ссылку на файл с архивом ФИАС
 * от сервиса информирования ФИАС.
 */
final class BaseFiasInformer implements FiasInformer
{
    private readonly HttpTransport $transport;

    private readonly string $endpointAll;

    private readonly string $endpointLast;

    public function __construct(
        HttpTransport $transport,
        string|FiasLinks $endpointAll = FiasLinks::INFORMER_ALL,
        string|FiasLinks $endpointLast = FiasLinks::INFORMER_LAST
    ) {
        $this->transport = $transport;
        $this->endpointAll = $endpointAll instanceof FiasLinks ? $endpointAll->value : $endpointAll;
        $this->endpointLast = $endpointLast instanceof FiasLinks ? $endpointLast->value : $endpointLast;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentCompleteVersion(): InformerResponse
    {
        return InformerResponseFactory::createFullFromJson(
            $this->query($this->endpointLast)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getNextDeltaVersion(int $currentVersion): ?InformerResponse
    {
        $deltas = $this->getAllDeltaVersions();

        $result = null;
        foreach ($deltas as $delta) {
            if ($delta->getVersion() > $currentVersion) {
                $result = $delta;
                break;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllDeltaVersions(): array
    {
        $data = $this->query($this->endpointAll);

        $list = [];
        foreach ($data as $item) {
            if (\is_array($item) && !empty($item['GarXMLDeltaURL'])) {
                // похоже только так это работает, дельта появляется не сразу
                // поэтому просто пропускаем объекты без дельты
                $list[] = InformerResponseFactory::createDeltaFromJson($item);
            }
        }

        usort(
            $list,
            fn (InformerResponse $a, InformerResponse $b): int => $a->getVersion() - $b->getVersion()
        );

        return $list;
    }

    /**
     * Отправляет запрос и проверяет ответ.
     */
    private function query(string $url): array
    {
        try {
            $response = $this->transport->get($url);
        } catch (\Throwable $e) {
            throw FiasInformerException::wrap($e);
        }

        if (!$response->isOk()) {
            throw FiasInformerException::create('Informer responsed with bad status: %s', $response->getStatusCode());
        }

        try {
            $data = $response->getJsonPayload();
        } catch (\Throwable $e) {
            throw FiasInformerException::wrap($e);
        }

        if (!\is_array($data)) {
            throw FiasInformerException::create('Response from informer is malformed');
        }

        return $data;
    }
}
