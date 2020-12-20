<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Объект, который преобразует имена полей ФИАС при трансформации xml строки в объект.
 */
class FiasNameConverter implements NameConverterInterface
{
    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function normalize(string $propertyName): string
    {
        $propertyName = trim($propertyName);
        $return = $propertyName;

        if (strpos($propertyName, '@') !== 0) {
            $return = '@' . $propertyName;
        }

        return $return;
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function denormalize(string $propertyName): string
    {
        $propertyName = trim($propertyName);
        $return = $propertyName;

        if (strpos($propertyName, '@') === 0) {
            $return = substr($propertyName, 1);
        }

        return $return;
    }
}
