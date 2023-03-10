<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Helper\ArrayHelper;

/**
 * Фабричный класс, который может создавать поле сущности.
 */
final class FiasEntityFieldFactory
{
    private function __construct()
    {
    }

    /**
     * Создает поле сущности из массива с данными.
     */
    public static function createFromArray(array $options): FiasEntityField
    {
        return new FiasEntityFieldImpl(
            FiasEntityFieldType::from(ArrayHelper::extractStringFromArrayByName('type', $options)),
            FiasEntityFieldSubType::from(ArrayHelper::extractStringFromArrayByName('subType', $options)),
            ArrayHelper::extractStringFromArrayByName('name', $options),
            ArrayHelper::extractStringFromArrayByName('description', $options),
            ArrayHelper::extractIntFromArrayByName('length', $options) ?: null,
            ArrayHelper::extractBoolFromArrayByName('isNullable', $options),
            ArrayHelper::extractBoolFromArrayByName('isPrimary', $options),
            ArrayHelper::extractBoolFromArrayByName('isIndex', $options),
            ArrayHelper::extractBoolFromArrayByName('isPartition', $options)
        );
    }
}
