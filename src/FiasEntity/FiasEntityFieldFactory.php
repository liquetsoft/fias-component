<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

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
            FiasEntityFieldType::from(self::extractStringFromArrayByName('type', $options)),
            FiasEntityFieldSubType::from(self::extractStringFromArrayByName('subType', $options)),
            self::extractStringFromArrayByName('name', $options),
            self::extractStringFromArrayByName('description', $options),
            self::extractIntFromArrayByName('length', $options),
            self::extractBoolFromArrayByName('isNullable', $options),
            self::extractBoolFromArrayByName('isPrimary', $options),
            self::extractBoolFromArrayByName('isIndex', $options),
            self::extractBoolFromArrayByName('isPartition', $options)
        );
    }

    /**
     * Извлекает строку из указанного массива по имени ключа.
     */
    private static function extractStringFromArrayByName(string $name, array $array): string
    {
        return trim((string) ($array[$name] ?? ''));
    }

    /**
     * Извлекает число из указанного массива по имени ключа.
     */
    private static function extractIntFromArrayByName(string $name, array $array): ?int
    {
        return isset($array[$name]) ? (int) $array[$name] : null;
    }

    /**
     * Извлекает булево значение из указанного массива по имени ключа.
     */
    private static function extractBoolFromArrayByName(string $name, array $array): bool
    {
        return (bool) ($array[$name] ?? false);
    }
}
