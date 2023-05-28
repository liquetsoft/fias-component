<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasSerializer;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Объект для преобразования xml-строк из ФИАС в объекты.
 *
 * @psalm-suppress DeprecatedInterface
 */
final class FiasSerializer extends Serializer
{
    /**
     * @param array<DenormalizerInterface|NormalizerInterface>|null $normalizers
     * @param array<DecoderInterface|EncoderInterface>|null         $encoders
     */
    public function __construct(array $normalizers = null, array $encoders = null)
    {
        if ($normalizers === null) {
            $normalizers = [
                new DateTimeNormalizer(),
                new ObjectNormalizer(
                    null,
                    new FiasSerializerNameConverter(),
                    null,
                    new ReflectionExtractor()
                ),
            ];
        }

        if ($encoders === null) {
            $encoders = [
                new XmlEncoder(),
            ];
        }

        parent::__construct($normalizers, $encoders);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $format !== null && strtolower($format) === 'xml';
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (\is_array($data)) {
            $data = $this->filterData($data);
        }

        return parent::denormalize($data, $type, $format, $context);
    }

    /**
     * Убирает пустые строки из массива - сериалайзер не понимает как их преобразовывать в другие типы.
     */
    private function filterData(array $data): mixed
    {
        $filteredData = [];
        foreach ($data as $name => $value) {
            if ($value !== '') {
                $filteredData[$name] = $value;
            }
        }

        return $filteredData;
    }
}
