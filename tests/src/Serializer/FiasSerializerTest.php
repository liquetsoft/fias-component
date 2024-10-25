<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Serializer\FiasSerializerFormat;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\Mock\FiasSerializerMock;

/**
 * Тест для объекта, который преобразует данные из xml ФИАС в объекты.
 *
 * @internal
 */
final class FiasSerializerTest extends BaseCase
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
    KOD_T_ST="227010000010000016740025000000000"
    EMPTYSTRINGINT=""
/>
EOT;

        $serializer = new FiasSerializer();
        $object = $serializer->deserialize(
            $data,
            FiasSerializerMock::class,
            FiasSerializerFormat::XML->value
        );

        $this->assertInstanceOf(FiasSerializerMock::class, $object);
        $this->assertSame(2, $object->getActstatid());
        $this->assertSame('Не актуальный', $object->getName());
        $this->assertSame('227010000010000016740025000000000', $object->getKodtst());
        $this->assertSame(0, $object->getEmptyStringInt());

        $date = $object->getTestDate();
        $date = $date ? $date->format('Y-m-d H:i:s') : null;
        $this->assertSame('2019-10-10 10:10:10', $date);
    }
}
