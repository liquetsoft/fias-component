<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFileImpl;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Нормалайзер, который преобразует объект файла из архива в массив.
 */
final class FiasUnpackerFileNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if (!($object instanceof UnpackerFile)) {
            throw new InvalidArgumentException("Instance of '" . UnpackerFile::class . "' is expected");
        }

        return [
            'archiveFile' => $object->getArchiveFile()->getPathname(),
            'name' => $object->getName(),
            'index' => $object->getIndex(),
            'size' => $object->getSize(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UnpackerFile;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            UnpackerFileImpl::class => true,
        ];
    }
}
