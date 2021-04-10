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
     */
    public function testNormalize(): void
    {
        $name = ' @TEST';
        $name1 = 'tEst1 ';

        $converter = new FiasNameConverter();

        $this->assertSame('@TEST', $converter->normalize($name));
        $this->assertSame('@tEst1', $converter->normalize($name1));
    }

    /**
     * Проверяет, что объект верно преобразует имя из XML.
     */
    public function testDenormalize(): void
    {
        $name = ' @TEST';
        $name1 = 'tEst1 ';

        $converter = new FiasNameConverter();

        $this->assertSame('TEST', $converter->denormalize($name));
        $this->assertSame('tEst1', $converter->denormalize($name1));
    }
}
