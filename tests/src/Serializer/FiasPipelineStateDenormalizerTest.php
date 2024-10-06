<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Serializer\FiasPipelineStateDenormalizer;
use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Тест для объекта, который преобразует массив в объект состояния.
 *
 * @internal
 */
final class FiasPipelineStateDenormalizerTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно денормализует данные.
     *
     * @dataProvider provideDenormalize
     */
    public function testDenormalize(mixed $data, array $expectedParams, bool $expectedIsCompleted = false): void
    {
        $denormalizer = new FiasPipelineStateDenormalizer();
        $res = $denormalizer->denormalize($data, State::class);

        $this->assertInstanceOf(State::class, $res);

        $resParams = [];
        foreach (StateParameter::cases() as $case) {
            $value = $res->getParameter($case, null);
            if ($value !== null) {
                $resParams[$case->value] = $value;
            }
        }

        $this->assertSame($expectedParams, $resParams);
        $this->assertSame($expectedIsCompleted, $res->isCompleted());
    }

    public static function provideDenormalize(): array
    {
        return [
            'scalar only params' => [
                [
                    'parameters' => [
                        StateParameter::FIAS_NEXT_VERSION_FULL_URL->value => 'http://test.full',
                        StateParameter::FIAS_NEXT_VERSION_DELTA_URL->value => 'http://test.delta',
                    ],
                ],
                [
                    StateParameter::FIAS_NEXT_VERSION_FULL_URL->value => 'http://test.full',
                    StateParameter::FIAS_NEXT_VERSION_DELTA_URL->value => 'http://test.delta',
                ],
            ],
            'array of scalars' => [
                [
                    'parameters' => [
                        StateParameter::FILES_TO_PROCEED->value => [
                            'file_1.txt',
                            'file_2.txt',
                        ],
                    ],
                ],
                [
                    StateParameter::FILES_TO_PROCEED->value => [
                        'file_1.txt',
                        'file_2.txt',
                    ],
                ],
            ],
            'empty data' => [
                [],
                [],
                false,
            ],
            'isCompleted true' => [
                [
                    'isCompleted' => true,
                ],
                [],
                true,
            ],
            'isCompleted false' => [
                [
                    'isCompleted' => false,
                ],
                [],
                false,
            ],
        ];
    }

    /**
     * Проверяет, что объект передаст управление вложенному денормалайзеру.
     */
    public function testDenormalizeAware(): void
    {
        $nestedClass = \stdClass::class;
        $nestedData = 'nested data';
        $data = [
            'parameters' => [
                StateParameter::FILES_TO_PROCEED->value => [
                    'class' => $nestedClass,
                    'data' => $nestedData,
                ],
            ],
        ];
        $format = FiasSerializerFormat::XML->value;
        $context = [
            'test_key_context' => 'test_value_context',
        ];
        $nestedReturn = 'test_return';

        $nestedDenormalizer = $this->mock(DenormalizerInterface::class);
        $nestedDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                $this->identicalTo($nestedData),
                $this->identicalTo($nestedClass),
                $this->identicalTo($format),
                $this->identicalTo($context)
            )
            ->willReturn($nestedReturn);

        $denormalizer = new FiasPipelineStateDenormalizer();
        $denormalizer->setDenormalizer($nestedDenormalizer);

        $res = $denormalizer->denormalize($data, State::class, $format, $context);

        $this->assertInstanceOf(State::class, $res);
        $this->assertSame($nestedReturn, $res->getParameter(StateParameter::FILES_TO_PROCEED));
    }

    /**
     * Проверяет, что объект првильно определит, что данные могут быть обработаны.
     *
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(string $type, bool $expected): void
    {
        $denormalizer = new FiasPipelineStateDenormalizer();

        $res = $denormalizer->supportsDenormalization([], $type, 'json', []);

        $this->assertSame($expected, $res);
    }

    public static function provideSupportsDenormalization(): array
    {
        return [
            'State interface heir' => [
                ArrayState::class,
                true,
            ],
            'State interface' => [
                State::class,
                true,
            ],
            'random class' => [
                \stdClass::class,
                false,
            ],
            'random string' => [
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
        $denormalizer = new FiasPipelineStateDenormalizer();

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
