<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasNameConverter;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который преобразует имена их xml.
 *
 * @internal
 */
class FiasNameConverterTest extends BaseCase
{
    /**
     * Проверяет, что объект верно преобразует имя.
     *
     * @dataProvider provideNormalize
     */
    public function testNormalize(string $name, string $awaits): void
    {
        $converter = new FiasNameConverter();

        $this->assertSame($awaits, $converter->normalize($name));
    }

    public function provideNormalize(): array
    {
        return [
            'with leading and tailing spaces' => ['   @test ', '@test'],
            'with at' => ['@test', '@test'],
            'without at' => ['test', '@test'],
            'utf' => ['тест', '@тест'],
        ];
    }

    /**
     * Проверяет, что объект верно преобразует имя из XML.
     *
     * @dataProvider provideDenormalize
     */
    public function testDenormalize(string $name, string $awaits): void
    {
        $converter = new FiasNameConverter();

        $this->assertSame($awaits, $converter->denormalize($name));
    }

    public function provideDenormalize(): array
    {
        return [
            'with leading and tailing spaces' => ['   @test ', 'test'],
            'with at' => ['@test', 'test'],
            'without at' => ['test', 'test'],
            'utf' => ['@тест', 'тест'],
        ];
    }
}
