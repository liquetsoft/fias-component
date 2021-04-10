<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use DateTimeImmutable;
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
     * Проверяет, что объект правильно разберет данные их xml в объект.
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
        $this->assertEquals(new DateTimeImmutable('2019-10-10T10:10:10.02'), $object->getTestDate());
        $this->assertSame(0, $object->getEmptyStringInt());
    }
}
