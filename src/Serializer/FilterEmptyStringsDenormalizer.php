<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Денормалайзер, который фильтрует пустые строки и передает оставшиеся для дальнейшей обработки.
 */
final class FilterEmptyStringsDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    private ?DenormalizerInterface $denormalizer = null;

    /**
     * {@inheritdoc}
     */
    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (SerializerFormat::XML->isEqual($format) && \is_array($data)) {
            $filteredData = [];
            foreach ($data as $key => $value) {
                if ($value !== '') {
                    $filteredData[$key] = $value;
                }
            }
        } else {
            $filteredData = $data;
        }

        if ($this->denormalizer !== null) {
            return $this->denormalizer->denormalize($filteredData, $type, $format, $context);
        } else {
            return $filteredData;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (SerializerFormat::XML->isEqual($format) && \is_array($data)) {
            foreach ($data as $value) {
                if ($value === '') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        if (SerializerFormat::XML->isEqual($format)) {
            return [
                '*' => false,
            ];
        }

        return [];
    }
}
