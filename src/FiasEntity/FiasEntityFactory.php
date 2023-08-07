<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;
use Liquetsoft\Fias\Component\Helper\ArrayHelper;

/**
 * Фабрика, которая создает описания сущностей ФИАС.
 */
final class FiasEntityFactory
{
    private const DEFAULT_PARTITION_COUNT = 1;

    private function __construct()
    {
    }

    /**
     * Создает описание сущности из массива, который хранится в папке resources.
     */
    public static function createFromArray(mixed $array): FiasEntity
    {
        if (!\is_array($array)) {
            throw FiasEntityException::create('Param must be an instance of array');
        }

        return new FiasEntityImpl(
            ArrayHelper::extractStringFromArrayByName('name', $array),
            ArrayHelper::extractStringFromArrayByName('xmlPath', $array),
            self::extractFields($array),
            ArrayHelper::extractStringFromArrayByName('description', $array),
            ArrayHelper::extractIntFromArrayByName('partitionsCount', $array, self::DEFAULT_PARTITION_COUNT),
            ArrayHelper::extractStringFromArrayByName('insertFileMask', $array),
            ArrayHelper::extractStringFromArrayByName('deleteFileMask', $array)
        );
    }

    /**
     * Извлекает массив с полями для сущности.
     *
     * @return FiasEntityField[]
     */
    private static function extractFields(array $array): array
    {
        $rawFields = ArrayHelper::extractArrayFromArrayByName('fields', $array);
        $fields = [];
        foreach ($rawFields as $key => $rawField) {
            if (\is_array($rawField)) {
                $rawField['name'] = $rawField['name'] ?? $key;
                $fields[] = FiasEntityFieldFactory::createFromArray($rawField);
            } elseif ($rawField instanceof FiasEntityField) {
                $fields[] = $rawField;
            } else {
                throw FiasEntityException::create('Field must be an array or implements EntityField');
            }
        }

        return $fields;
    }
}
