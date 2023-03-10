<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Helper\ArrayHelper;

/**
 * Фабрика, которая создает описания сущностей ФИАС.
 */
final class FiasEntityFactory
{
    private function __construct()
    {
    }

    /**
     * Создает описание сущности из массива, который хранится в папке resources.
     */
    public static function createFromArray(mixed $array): FiasEntity
    {
        if (!\is_array($array)) {
            throw new \InvalidArgumentException('Param must be an instance of array');
        }

        return new FiasEntityImpl(
            ArrayHelper::extractStringFromArrayByName('name', $array),
            ArrayHelper::extractStringFromArrayByName('xmlPath', $array),
            self::extractFields($array),
            ArrayHelper::extractStringFromArrayByName('description', $array),
            ArrayHelper::extractIntFromArrayByName('partitionsCount', $array, 1),
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
        foreach ($rawFields as $rawField) {
            if (\is_array($rawField)) {
                $fields[] = FiasEntityFieldFactory::createFromArray($rawField);
            } elseif ($rawField instanceof FiasEntityField) {
                $fields[] = $rawField;
            } else {
                throw new \InvalidArgumentException('Field must be an array or implements EntityField');
            }
        }

        return $fields;
    }
}
