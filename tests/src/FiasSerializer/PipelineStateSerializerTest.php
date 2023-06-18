<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasSerializer;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\FiasSerializer\PipelineStateSerializer;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use Liquetsoft\Fias\Component\Tests\SerializerCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Тест для объекта, который преобразует FiasFileSelectorFile в массив и обратно.
 *
 * @internal
 */
class PipelineStateSerializerTest extends BaseCase
{
    use PipelineCase;
    use SerializerCase;

    /**
     * Проверяет, что объект правильно нормализует данные.
     *
     * @dataProvider provideNormalize
     */
    public function testNormalize(mixed $object, ?NormalizerInterface $owningNormalizer, array|\Exception $awaits): void
    {
        $serializer = new PipelineStateSerializer();

        if ($owningNormalizer) {
            $serializer->setNormalizer($owningNormalizer);
        }

        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }

        $res = $serializer->normalize($object);

        if (!($awaits instanceof \Exception)) {
            $this->assertSame($awaits, $res);
        }
    }

    public function provideNormalize(): array
    {
        $fileToProceed = new \stdClass();

        return [
            'scalar target' => [
                'test',
                null,
                new InvalidArgumentException('Object must have ' . PipelineState::class . ' type'),
            ],
            'scalar param' => [
                $this->createPipelineStateMock(
                    [
                        PipelineStateParam::FIAS_VERSION->value => 123,
                        PipelineStateParam::DOWNLOAD_TO_FILE->value => '/path',
                    ]
                ),
                null,
                [
                    PipelineStateParam::FIAS_VERSION->value => 123,
                    PipelineStateParam::DOWNLOAD_TO_FILE->value => '/path',
                ],
            ],
            'object param' => [
                $this->createPipelineStateMock(
                    [
                        PipelineStateParam::DOWNLOAD_TO_FILE->value => $fileToProceed,
                    ]
                ),
                $this->createNormalizerMockAwaitNormalization($fileToProceed, 'test'),
                [
                    PipelineStateParam::DOWNLOAD_TO_FILE->value => 'test',
                ],
            ],
            'array param' => [
                $this->createPipelineStateMock(
                    [
                        PipelineStateParam::FILES_TO_PROCEED->value => ['test1', 'test2'],
                    ]
                ),
                null,
                [
                    PipelineStateParam::FILES_TO_PROCEED->value => ['test1', 'test2'],
                ],
            ],
        ];
    }

    /**
     * Проверяет, что объект правильно определит цель для нормализации.
     *
     * @dataProvider provideSupportsNormalization
     */
    public function testSupportsNormalization(mixed $object, bool $awaits): void
    {
        $serializer = new PipelineStateSerializer();

        $this->assertSame($awaits, $serializer->supportsNormalization($object));
    }

    public function provideSupportsNormalization(): array
    {
        return [
            'scalar' => ['test', false],
            'different object type' => [$this, false],
            'correct type' => [
                $this->createPipelineStateMock(),
                true,
            ],
        ];
    }

    /**
     * Проверяет, что объект правильно нормализует данные.
     *
     * @dataProvider provideDeormalize
     */
    public function testDeormalize(mixed $data, string $type, ?DenormalizerInterface $owningDenormalizer, array|\Exception $awaits): void
    {
        $serializer = new PipelineStateSerializer();

        if ($owningDenormalizer) {
            $serializer->setDenormalizer($owningDenormalizer);
        }

        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }

        $res = $serializer->denormalize($data, $type);

        if (!($awaits instanceof \Exception)) {
            $this->assertInstanceOf(PipelineState::class, $res);
            $stateArray = [];
            foreach (PipelineStateParam::cases() as $case) {
                $value = $res->get($case);
                if ($value !== null) {
                    $stateArray[$case->value] = $value;
                }
            }
            $this->assertSame($awaits, $stateArray);
        }
    }

    public function provideDeormalize(): array
    {
        $fileToProceed = new \stdClass();

        return [
            'wrong type' => [
                [],
                'test',
                null,
                new InvalidArgumentException('Type must be ' . PipelineState::class),
            ],
            'wrong data' => [
                'test',
                PipelineState::class,
                null,
                new InvalidArgumentException('Data must be an array instance'),
            ],
            'scalar param' => [
                [
                    PipelineStateParam::DOWNLOAD_TO_FILE->value => 'test',
                    'test' => 'test',
                ],
                PipelineState::class,
                null,
                [
                    PipelineStateParam::DOWNLOAD_TO_FILE->value => 'test',
                ],
            ],
            'proceed files param' => [
                [
                    PipelineStateParam::FILES_TO_PROCEED->value => [
                        ['test' => 'test'],
                    ],
                ],
                PipelineState::class,
                $this->createDeormalizerMockAwaitDenormalization(
                    ['test' => 'test'],
                    FiasFileSelectorFile::class,
                    $fileToProceed,
                ),
                [
                    PipelineStateParam::FILES_TO_PROCEED->value => [$fileToProceed],
                ],
            ],
            'proceed files param non array' => [
                [
                    PipelineStateParam::FILES_TO_PROCEED->value => 'test',
                ],
                PipelineState::class,
                $this->createDeormalizerMockAwaitDenormalization(
                    ['test' => 'test'],
                    FiasFileSelectorFile::class,
                    $fileToProceed,
                ),
                [
                    PipelineStateParam::FILES_TO_PROCEED->value => [],
                ],
            ],
            'proceed files param no owning serializer' => [
                [
                    PipelineStateParam::FILES_TO_PROCEED->value => 'test',
                ],
                PipelineState::class,
                null,
                [
                    PipelineStateParam::FILES_TO_PROCEED->value => [],
                ],
            ],
        ];
    }

    /**
     * Проверяет, что объект правильно определит цель для денормализации.
     *
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(mixed $data, string $type, bool $awaits): void
    {
        $serializer = new PipelineStateSerializer();

        $this->assertSame($awaits, $serializer->supportsDenormalization($data, $type));
    }

    public function provideSupportsDenormalization(): array
    {
        return [
            'scalar type' => [[], 'test', false],
            'different object type' => [[], self::class, false],
            'non array data' => ['test', PipelineState::class, false],
            'correct type' => [[], PipelineState::class, true],
            'correct type with slashes' => [[], '\\' . PipelineState::class . '\\', true],
        ];
    }
}
