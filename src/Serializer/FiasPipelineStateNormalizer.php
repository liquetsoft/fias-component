<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Serializer;

use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Нормалайзер, который преобразует объект состояния в массив.
 */
final class FiasPipelineStateNormalizer implements NormalizerAwareInterface, NormalizerInterface
{
    private ?NormalizerInterface $normalizer = null;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if (!($object instanceof State)) {
            throw new InvalidArgumentException("Instance of '" . State::class . "' is expected");
        }

        $parameters = [];
        foreach (StateParameter::cases() as $case) {
            $stateValue = $object->getParameter($case, null);
            $value = $this->prepareStateParameterValue($stateValue, $format, $context);
            if ($value !== null) {
                $parameters[$case->value] = $value;
            }
        }

        return [
            'parameters' => $parameters,
            'isCompleted' => $object->isCompleted(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof State;
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
     * Приводит значение параметра к состоянию пригодному для отправки в json.
     */
    private function prepareStateParameterValue(mixed $value, ?string $format, array $context): mixed
    {
        if (\is_array($value)) {
            return array_map(
                fn (mixed $item): mixed => $this->prepareStateParameterValue($item, $format, $context),
                $value
            );
        } elseif (\is_object($value) && $this->normalizer !== null) {
            return [
                'class' => \get_class($value),
                'data' => $this->normalizer->normalize($value, $format, $context),
            ];
        }

        return $value;
    }
}
