<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Liquetsoft\Fias\Component\FiasFile\FiasFile;
use Liquetsoft\Fias\Component\FiasFile\FiasFileFactory;
use Liquetsoft\Fias\Component\FiasFile\FiasFileImpl;
use Liquetsoft\Fias\Component\Helper\ArrayHelper;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Денормалайзер, который преобразует массив в объект файла.
 */
final class FiasFileDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $data = \is_array($data) ? $data : [];

        $name = trim(ArrayHelper::extractStringFromArrayByName('name', $data));
        if ($name === '') {
            throw new InvalidArgumentException("'name' param isn't set");
        }

        $size = ArrayHelper::extractIntFromArrayByName('size', $data);

        return FiasFileFactory::create($name, $size);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_a($type, FiasFile::class, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            FiasFileImpl::class => true,
        ];
    }
}
