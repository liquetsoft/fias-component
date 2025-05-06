<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Денормалайзер, который преобразует массив в объект состояния.
 */
final class FiasPipelineStateDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    private ?DenormalizerInterface $denormalizer = null;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        /** @var array */
        $parameters = \is_array($data) && isset($data['parameters']) && \is_array($data['parameters'])
            ? $data['parameters']
            : [];

        /** @var bool */
        $isCompleted = \is_array($data) && isset($data['isCompleted'])
            ? (bool) $data['isCompleted']
            : false;

        $preparedParameters = [];
        foreach (StateParameter::cases() as $case) {
            $parameterValue = $parameters[$case->value] ?? null;
            $preparedValue = $this->prepareStateParameterValue($parameterValue, $format, $context);
            if ($preparedValue !== null) {
                $preparedParameters[$case->value] = $preparedValue;
            }
        }

        return new ArrayState($preparedParameters, $isCompleted);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return State::class === $type || is_a($type, State::class, true);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            State::class => true,
        ];
    }

    /**
     * Приводит значение параметра к состоянию пригодному для использования в объекте.
     */
    private function prepareStateParameterValue(mixed $value, ?string $format, array $context): mixed
    {
        if (\is_array($value) && $this->denormalizer !== null && isset($value['class'], $value['data'])) {
            return $this->denormalizer->denormalize(
                $value['data'],
                (string) $value['class'],
                $format,
                $context
            );
        } elseif (\is_array($value)) {
            return array_map(
                fn (mixed $item): mixed => $this->prepareStateParameterValue($item, $format, $context),
                $value
            );
        }

        return $value;
    }
}
