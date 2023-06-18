<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasSerializer;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFileFactory;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Объект, который преобразует FiasFileSelectorFile в массив и обратно.
 */
final class FiasFileSelectorFileSerializer implements DenormalizerInterface, NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        if (!($object instanceof FiasFileSelectorFile)) {
            throw new InvalidArgumentException('Object must have ' . FiasFileSelectorFile::class . ' type');
        }

        return [
            'path' => $object->getPath(),
            'size' => $object->getSize(),
            'pathToArchive' => $object->isArchived() ? $object->getPathToArchive() : null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = [])
    {
        return $data instanceof FiasFileSelectorFile;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if ($this->trimType($type) !== FiasFileSelectorFile::class) {
            throw new InvalidArgumentException('Type must be ' . FiasFileSelectorFile::class);
        }

        if (!\is_array($data)) {
            throw new InvalidArgumentException('Data must be an array instance');
        }

        return FiasFileSelectorFileFactory::createFromArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = [])
    {
        return $this->trimType($type) === FiasFileSelectorFile::class;
    }

    /**
     * Нормализует строку с типом для сравнения.
     */
    private function trimType(string $type): string
    {
        return trim($type, " \n\r\t\v\x00\\");
    }
}
