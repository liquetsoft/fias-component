<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Serializer\FiasPipelineStateNormalizer;
use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Тест для объекта, который преобразует объект состояния в массив.
 *
 * @internal
 */
class FiasPipelineStateNormalizerTest extends BaseCase
{
    /**
     * Проверяет, что объект верно преобразует состояние.
     *
     * @dataProvider provideNormalize
     */
    public function testNormalize(object $object, array|\Exception $expected): void
    {
        $normalizer = new FiasPipelineStateNormalizer();

        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $res = $normalizer->normalize($object, 'json', []);

        if (!($expected instanceof \Exception)) {
            $this->assertSame($expected, $res);
        }
    }

    public static function provideNormalize(): array
    {
        return [
            'wrong object type exception' => [
                new \stdClass(),
                new InvalidArgumentException(State::class),
            ],
            'empty state' => [
                new ArrayState(),
                [
                    'parameters' => [],
                    'isCompleted' => false,
                ],
            ],
            'state with scalars only' => [
                new ArrayState(
                    [
                        StateParameter::FIAS_NEXT_VERSION_DELTA_URL->value => 'http://test.delta',
                        StateParameter::FIAS_NEXT_VERSION_FULL_URL->value => 'http://test.full',
                    ],
                    true
                ),
                [
                    'parameters' => [
                        StateParameter::FIAS_NEXT_VERSION_FULL_URL->value => 'http://test.full',
                        StateParameter::FIAS_NEXT_VERSION_DELTA_URL->value => 'http://test.delta',
                    ],
                    'isCompleted' => true,
                ],
            ],
            'state with array of scalars' => [
                new ArrayState(
                    [
                        StateParameter::FILES_TO_PROCEED->value => [
                            'file_1.txt',
                            'file_2.txt',
                            'file_3.txt',
                        ],
                    ]
                ),
                [
                    'parameters' => [
                        StateParameter::FILES_TO_PROCEED->value => [
                            'file_1.txt',
                            'file_2.txt',
                            'file_3.txt',
                        ],
                    ],
                    'isCompleted' => false,
                ],
            ],
        ];
    }

    /**
     * Проверяет, что объект передаст управление вложенному нормалайзеру.
     */
    public function testNormalizeAware(): void
    {
        $object = new \stdClass();
        $state = new ArrayState(
            [
                StateParameter::FILES_TO_PROCEED->value => $object,
            ]
        );
        $format = FiasSerializerFormat::XML->value;
        $context = [
            'test_key_context' => 'test_value_context'
        ];
        $nestedReturn = 'nested_return';
        $expectedReturn = [
            'parameters' => [
                StateParameter::FILES_TO_PROCEED->value => [
                    'class' => \get_class($object),
                    'data' => $nestedReturn,
                ],
            ],
            'isCompleted' => $state->isCompleted(),
        ];

        $nestedNormalizer = $this->mock(NormalizerInterface::class);
        $nestedNormalizer->expects($this->once())
            ->method('normalize')
            ->with(
                $this->identicalTo($object),
                $this->identicalTo($format),
                $this->identicalTo($context)
            )
            ->willReturn($nestedReturn);

        $denormalizer = new FiasPipelineStateNormalizer();
        $denormalizer->setNormalizer($nestedNormalizer);

        $res = $denormalizer->normalize($state, $format, $context);

        $this->assertSame($expectedReturn, $res);
    }

    /**
     * Проверяет, что объект првильно определит, что данные могут быть обработаны.
     *
     * @dataProvider provideSupportsNormalization
     */
    public function testSupportsNormalization(mixed $data, bool $expected): void
    {
        $normalizer = new FiasPipelineStateNormalizer();

        $res = $normalizer->supportsNormalization($data, 'json', []);

        $this->assertSame($expected, $res);
    }

    public static function provideSupportsNormalization(): array
    {
        return [
            'state object' => [
                new ArrayState(),
                true,
            ],
            'not a state object' => [
                new \stdClass(),
                false,
            ],
            'scalar value' => [
                'test',
                false,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет корректный список поддерживаемых типов.
     *
     * @dataProvider provideGetSupportedTypes
     */
    public function testGetSupportedTypes(string $format, array $expected): void
    {
        $denormalizer = new FiasPipelineStateNormalizer();

        $res = $denormalizer->getSupportedTypes($format);

        $this->assertSame($expected, $res);
    }

    public static function provideGetSupportedTypes(): array
    {
        return [
            'xml' => [
                FiasSerializerFormat::XML->value,
                [
                    State::class => true,
                ],
            ],
            'json' => [
                'json',
                [
                    State::class => true,
                ],
            ],
        ];
    }
}
