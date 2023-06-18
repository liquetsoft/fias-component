<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasSerializer;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateArray;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Объект, который преобразует FiasFileSelectorFile в массив и обратно.
 */
final class PipelineStateSerializer implements DenormalizerAwareInterface, DenormalizerInterface, NormalizerAwareInterface, NormalizerInterface
{
    private ?NormalizerInterface $owningNormalizer = null;

    private ?DenormalizerInterface $owningDenormalizer = null;

    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        if (!($object instanceof PipelineState)) {
            throw new InvalidArgumentException('Object must have ' . PipelineState::class . ' type');
        }

        return $this->normalizeStateArray(
            $this->convertStateToArray($object)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = [])
    {
        return $data instanceof PipelineState;
    }

    /**
     * {@inheritdoc}
     */
    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->owningNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if ($this->trimType($type) !== PipelineState::class) {
            throw new InvalidArgumentException('Type must be ' . PipelineState::class);
        }

        if (!\is_array($data)) {
            throw new InvalidArgumentException('Data must be an array instance');
        }

        $params = [];
        foreach (PipelineStateParam::cases() as $case) {
            $value = $data[$case->value] ?? null;
            if ($value === null) {
                continue;
            } elseif ($case === PipelineStateParam::FILES_TO_PROCEED) {
                $value = \is_array($value) ? $value : [];
                $params[$case->value] = [];
                foreach ($value as $file) {
                    if ($this->owningDenormalizer) {
                        $params[$case->value][] = $this->owningDenormalizer->denormalize(
                            $file,
                            FiasFileSelectorFile::class
                        );
                    }
                }
            } elseif (\is_scalar($value)) {
                $params[$case->value] = $value;
            }
        }

        return new PipelineStateArray($params);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = [])
    {
        return $this->trimType($type) === PipelineState::class && \is_array($data);
    }

    /**
     * {@inheritdoc}
     */
    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->owningDenormalizer = $denormalizer;
    }

    /**
     * Преобразует объект состояния в ассоциативный массив.
     */
    private function convertStateToArray(PipelineState $state): array
    {
        $stateArray = [];
        foreach (PipelineStateParam::cases() as $case) {
            $stateArray[$case->value] = $state->get($case);
        }

        return $stateArray;
    }

    /**
     * Нормализует все элементы массива полученные из объекта состояния.
     */
    private function normalizeStateArray(array $stateArray): array
    {
        $normalized = [];
        foreach ($stateArray as $key => $value) {
            if (\is_scalar($value)) {
                $normalized[$key] = $value;
            } elseif (\is_array($value)) {
                $normalized[$key] = $this->normalizeStateArray($value);
            } elseif ($this->owningNormalizer?->supportsNormalization($value)) {
                $normalized[$key] = $this->owningNormalizer->normalize($value);
            }
        }

        return $normalized;
    }

    /**
     * Нормализует строку с типом для сравнения.
     */
    private function trimType(string $type): string
    {
        return trim($type, " \n\r\t\v\x00\\");
    }
}
