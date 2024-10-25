<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Объект для преобразования xml-строк из ФИАС в объекты.
 */
final class FiasSerializer implements SerializerInterface
{
    private readonly Serializer $nestedSerializer;

    /**
     * @param array<DenormalizerInterface|NormalizerInterface>|null $normalizers
     * @param array<DecoderInterface|EncoderInterface>|null         $encoders
     */
    public function __construct(?array $normalizers = null, ?array $encoders = null)
    {
        if ($normalizers === null) {
            $normalizers = [
                new DateTimeNormalizer(),
                new FiasPipelineStateNormalizer(),
                new FiasPipelineStateDenormalizer(),
                new FiasUnpackerFileNormalizer(),
                new FiasUnpackerFileDenormalizer(),
                new FiasFileNormalizer(),
                new FiasFileDenormalizer(),
                new ObjectNormalizer(
                    nameConverter: new FiasNameConverter(),
                    propertyTypeExtractor: new ReflectionExtractor(),
                    defaultContext: [
                        ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                    ]
                ),
            ];
        }

        array_unshift($normalizers, new FiasFilterEmptyStringsDenormalizer());

        if ($encoders === null) {
            $encoders = [
                new XmlEncoder(
                    [
                        XmlEncoder::TYPE_CAST_ATTRIBUTES => false,
                    ]
                ),
            ];
        }

        $this->nestedSerializer = new Serializer($normalizers, $encoders);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(mixed $data, string $format, array $context = []): string
    {
        return $this->nestedSerializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress MixedReturnStatement
     */
    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        return $this->nestedSerializer->deserialize($data, $type, $format, $context);
    }
}
