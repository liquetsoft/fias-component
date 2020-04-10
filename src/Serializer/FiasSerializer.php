<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Преднастроенный объект сериализатора для ФИАС.
 */
class FiasSerializer extends Serializer
{
    /**
     * @param array|null $normalizers
     * @param array|null $encoders
     */
    public function __construct(?array $normalizers = null, ?array $encoders = null)
    {
        if ($normalizers === null) {
            $normalizers = [
                new DateTimeNormalizer(),
                new ObjectNormalizer(
                    null,
                    new FiasNameConverter(),
                    null,
                    new ReflectionExtractor(),
                    null,
                    null,
                    [
                        ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                    ]
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
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $data = $this->filterData($data);

        return parent::denormalize($data, $type, $format, $context);
    }

    /**
     * Removes items from with empty string in value from array.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function filterData($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $filteredData = [];
        foreach ($data as $name => $value) {
            if ($value !== '' || strpos($name, '@') !== 0) {
                $filteredData[$name] = $value;
            }
        }

        return $filteredData;
    }
}
