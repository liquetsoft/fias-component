<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Объект, который преобразует имена полей ФИАС для того, чтобы их можно было бы
 * передать в объект сериализатора.
 */
class FiasNameConverter implements NameConverterInterface
{
    /**
     * @inheritdoc
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
     * @inheritdoc
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
