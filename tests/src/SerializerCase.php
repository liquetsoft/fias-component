<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Трэйт, который содержит методы для создания моков логгера.
 */
trait SerializerCase
{
    /**
     * Создает мок для нормалайзера, который ожидает один объект для номализации.
     *
     * @return NormalizerInterface&MockObject
     */
    public function createNormalizerMockAwaitNormalization(mixed $target, mixed $result): NormalizerInterface
    {
        $mock = $this->createNormalizerMock();
        $mock->method('supportsNormalization')->willReturnCallback(
            fn (mixed $param): bool => $param === $target
        );
        $mock->method('normalize')->willReturnCallback(
            fn (mixed $param): mixed => $param === $target ? $result : null
        );

        return $mock;
    }

    /**
     * Создает мок для нормалайзера.
     *
     * @return NormalizerInterface&MockObject
     */
    public function createNormalizerMock(): NormalizerInterface
    {
        return $this->getMockBuilder(NormalizerInterface::class)->getMock();
    }

    /**
     * Создает мок для денормалайзера, который ожидает один объект для деномализации.
     *
     * @return DenormalizerInterface&MockObject
     */
    public function createDeormalizerMockAwaitDenormalization(mixed $target, string $type, mixed $result): DenormalizerInterface
    {
        $mock = $this->createDenormalizerMock();
        $mock->method('supportsDenormalization')->willReturnCallback(
            fn (mixed $param, string $paramType): bool => $param === $target && $paramType === $type
        );
        $mock->method('denormalize')->willReturnCallback(
            fn (mixed $param, string $paramType): mixed => $param === $target && $paramType === $type ? $result : null
        );

        return $mock;
    }

    /**
     * Создает мок для денормалайзера.
     *
     * @return DenormalizerInterface&MockObject
     */
    public function createDenormalizerMock(): DenormalizerInterface
    {
        return $this->getMockBuilder(DenormalizerInterface::class)->getMock();
    }
}
