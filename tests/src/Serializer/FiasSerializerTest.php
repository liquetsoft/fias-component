<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\Mock\FiasSerializerMock;

/**
 * Тест для объекта, который преобразует данные из xml ФИАС в объекты.
 *
 * @internal
 */
class FiasSerializerTest extends BaseCase
{
    /**
     * Проверяет, что объект не поддерживает нормализацию.
     */
    public function testSupportsNormalization(): void
    {
        $serializer = new FiasSerializer();

        $this->assertFalse($serializer->supportsNormalization('test'));
    }

    /**
     * Проверяет, что объект поддерживает денормализацию xml.
     *
     * @dataProvider provideSupportsDeormalization
     */
    public function testSupportsDeormalization(?string $format, bool $awaits): void
    {
        $serializer = new FiasSerializer();

        $this->assertSame($awaits, $serializer->supportsDenormalization('test', 'test', $format));
    }

    public function provideSupportsDeormalization(): array
    {
        return [
            'lower case' => ['xml', true],
            'upper case' => ['XML', true],
            'csv' => ['csv', false],
            'null' => [null, false],
        ];
    }

    /**
     * Проверяет, что объект правильно разберет данные из xml в объект.
     */
    public function testDenormalize(): void
    {
        $data = <<<EOT
<ActualStatus
    ACTSTATID="2"
    NAME="&#x41D;&#x435; &#x430;&#x43A;&#x442;&#x443;&#x430;&#x43B;&#x44C;&#x43D;&#x44B;&#x439;"
    TESTDATE="2019-10-10T10:10:10.02"
    KOD_T_ST="10"
    EMPTYSTRINGINT=""
/>
EOT;
        $serializer = new FiasSerializer();
        $object = $serializer->deserialize($data, FiasSerializerMock::class, 'xml');

        $this->assertInstanceOf(FiasSerializerMock::class, $object);
        $this->assertSame(2, $object->getActstatid());
        $this->assertSame('Не актуальный', $object->getName());
        $this->assertSame('10', $object->getKodtst());
        $this->assertSame(0, $object->getEmptyStringInt());
        $this->assertSame('2019-10-10 10:10:10', $object->getTestDate()?->format('Y-m-d H:i:s'));
    }
}
