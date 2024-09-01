<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use Liquetsoft\Fias\Component\Helper\ArrayHelper;

/**
 * Фабрика, которая может создать объект со ссылкой на версию ФИАС.
 */
final class FiasInformerResponseFactory
{
    private function __construct()
    {
    }

    /**
     * Создает объект для версии, используя json ответ от сервиса.
     */
    public static function createFromJson(array $data): FiasInformerResponse
    {
        return new FiasInformerResponseImpl(
            ArrayHelper::extractIntFromArrayByName('VersionId', $data),
            ArrayHelper::extractStringFromArrayByName('GarXMLFullURL', $data),
            ArrayHelper::extractStringFromArrayByName('GarXMLDeltaURL', $data)
        );
    }
}
