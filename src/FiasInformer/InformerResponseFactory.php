<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;

/**
 * Фабрика, которая может создать объект со ссылкой на версию ФИАС.
 */
final class InformerResponseFactory
{
    private function __construct()
    {
    }

    /**
     * Создает объект для версии, используя json ответ от сервиса.
     */
    public static function createFromJson(array $data): InformerResponse
    {
        $version = (int) ($data['VersionId'] ?? 0);
        if ($version === 0) {
            throw FiasInformerException::create('No version provided');
        }

        $fullUrl = (string) ($data['GarXMLFullURL'] ?? '');
        $deltaUrl = (string) ($data['GarXMLDeltaURL'] ?? '');

        return new BaseInformerResponse($version, $fullUrl, $deltaUrl);
    }
}
