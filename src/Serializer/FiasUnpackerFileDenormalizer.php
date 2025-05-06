<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Liquetsoft\Fias\Component\Helper\ArrayHelper;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFileFactory;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFileImpl;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Денормалайзер, который преобразует массив в объект файла архива.
 */
final class FiasUnpackerFileDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $data = \is_array($data) ? $data : [];

        $archiveFile = trim(ArrayHelper::extractStringFromArrayByName('archiveFile', $data));
        if ($archiveFile === '') {
            throw new InvalidArgumentException("'archiveFile' param isn't set");
        }

        $name = trim(ArrayHelper::extractStringFromArrayByName('name', $data));
        if ($name === '') {
            throw new InvalidArgumentException("'name' param isn't set");
        }

        $index = ArrayHelper::extractIntFromArrayByName('index', $data);
        $size = ArrayHelper::extractIntFromArrayByName('size', $data);

        return UnpackerFileFactory::create(
            new \SplFileInfo($archiveFile),
            $name,
            $index,
            $size
        );
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_a($type, UnpackerFile::class, true);
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
