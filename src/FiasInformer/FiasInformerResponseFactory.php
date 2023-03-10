<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

/**
 * Фабрика, которая может создать объект со ссылкой на версию ФИАС.
 */
final class FiasInformerResponseFactory
{
    private const DEFAULT_VERSION_NUMBER = 0;

    private function __construct()
    {
    }

    /**
     * Создает объект для версии, используя json ответ от сервиса.
     */
    public static function createFromJson(array $data): FiasInformerResponse
    {
        return new FiasInformerResponseImpl(
            (int) ($data['VersionId'] ?? self::DEFAULT_VERSION_NUMBER),
            (string) ($data['GarXMLFullURL'] ?? ''),
            (string) ($data['GarXMLDeltaURL'] ?? '')
        );
    }
}
