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
     * Создает объект, используя ссылку и версию.
     */
    public static function create(int $version, string $url): InformerResponse
    {
        return new BaseInformerResponse($version, $url);
    }

    /**
     * Создает объект для полной версии, используя json ответ от сервиса.
     */
    public static function createFullFromJson(array $data): InformerResponse
    {
        $version = (int) ($data['VersionId'] ?? 0);
        if ($version === 0) {
            throw FiasInformerException::create('No version provided');
        }

        $url = (string) ($data['GarXMLFullURL'] ?? '');
        if ($url === '') {
            throw FiasInformerException::create('No url provided');
        }

        return new BaseInformerResponse($version, $url);
    }

    /**
     * Создает объект для дельта версии, используя json ответ от сервиса.
     */
    public static function createDeltaFromJson(array $data): InformerResponse
    {
        $version = (int) ($data['VersionId'] ?? 0);
        if ($version === 0) {
            throw FiasInformerException::create('No version provided');
        }

        $url = (string) ($data['GarXMLDeltaURL'] ?? '');
        if ($url === '') {
            throw FiasInformerException::create('No url provided');
        }

        return new BaseInformerResponse($version, $url);
    }
}
