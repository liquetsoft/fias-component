<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Денормалайзер, который фильтрует пустые строки и передает оставшиеся для дальнейшей обработки.
 */
final class FiasFilterEmptyStringsDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    private ?DenormalizerInterface $denormalizer = null;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (FiasSerializerFormat::XML->isEqual($format) && \is_array($data)) {
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
    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (FiasSerializerFormat::XML->isEqual($format) && \is_array($data)) {
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
        if (FiasSerializerFormat::XML->isEqual($format)) {
            return [
                '*' => false,
            ];
        }

        return [];
    }
}
