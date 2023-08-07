<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasSerializer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Объект, который преобразует FiasFileSelectorFile в массив и обратно.
 */
final class SplFileInfoSerializer implements DenormalizerInterface, NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        if (!($object instanceof \SplFileInfo)) {
            throw new InvalidArgumentException('Object must have ' . \SplFileInfo::class . ' type');
        }

        return $object->getRealPath();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = [])
    {
        return $data instanceof \SplFileInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if ($this->trimType($type) !== \SplFileInfo::class) {
            throw new InvalidArgumentException('Type must be ' . \SplFileInfo::class);
        }

        if (!\is_string($data)) {
            throw new InvalidArgumentException('Data must be a string instance');
        }

        return new \SplFileInfo($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = [])
    {
        return $this->trimType($type) === \SplFileInfo::class && \is_string($data);
    }

    /**
     * Нормализует строку с типом для сравнения.
     */
    private function trimType(string $type): string
    {
        return trim($type, " \n\r\t\v\x00\\");
    }
}
