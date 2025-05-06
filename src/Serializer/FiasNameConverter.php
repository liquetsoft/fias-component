<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Объект, который преобразует имена полей ФИАС при трансформации xml строки в объект.
 *
 * @internal
 */
final class FiasNameConverter implements NameConverterInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function normalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string
    {
        if (!FiasSerializerFormat::XML->isEqual($format)) {
            return $propertyName;
        }

        $propertyName = trim($propertyName);
        if (strpos($propertyName, '@') !== 0) {
            return '@' . $propertyName;
        }

        return $propertyName;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function denormalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string
    {
        if (!FiasSerializerFormat::XML->isEqual($format)) {
            return $propertyName;
        }

        $propertyName = trim($propertyName);
        if (strpos($propertyName, '@') === 0) {
            return substr($propertyName, 1);
        }

        return $propertyName;
    }
}
