<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Liquetsoft\Fias\Component\FiasFile\FiasFile;
use Liquetsoft\Fias\Component\FiasFile\FiasFileImpl;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Нормалайзер, который преобразует объект файла в массив.
 */
final class FiasFileNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if (!($object instanceof FiasFile)) {
            throw new InvalidArgumentException("Instance of '" . FiasFile::class . "' is expected");
        }

        return [
            'name' => $object->getName(),
            'size' => $object->getSize(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FiasFile;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            FiasFileImpl::class => true,
        ];
    }
}
