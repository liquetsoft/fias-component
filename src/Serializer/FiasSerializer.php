<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Преднастроенный объект сериализатора для ФИАС.
 */
class FiasSerializer extends Serializer
{
    public function __construct(?array $normalizers = null, ?array $encoders = null)
    {
        if ($normalizers === null) {
            $normalizers = [
                new ObjectNormalizer(null, new FiasNameConverter),
            ];
        }

        if ($encoders === null) {
            $encoders = [
                new XmlEncoder,
            ];
        }

        parent::__construct($normalizers, $encoders);
    }
}
