<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use DateTimeInterface;
use DateTime;

/**
 * Тест для объекта, который сереализует данные из ФИАС.
 */
class FiasSerializerTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно разберет данные их xml в объект.
     */
    public function testDenormalize()
    {
        $data = '<ActualStatus ACTSTATID="2" NAME="Не актуальный" TESTDATE="2019-10-10T10:10:10.02"/>';
        $serializer = new FiasSerializer;

        $object = $serializer->deserialize($data, FiasSerializerObject::class, 'xml');

        $this->assertInstanceOf(FiasSerializerObject::class, $object);
        $this->assertSame(2, $object->getActstatid());
        $this->assertSame('Не актуальный', $object->getName());
        $this->assertEquals(new DateTime('2019-10-10T10:10:10.02'), $object->getTestDate());
    }
}

/**
 * Мок для проверки сериализатора.
 */
class FiasSerializerObject
{
    private $ACTSTATID;
    private $name;
    private $testDate;

    public function setActstatid(int $ACTSTATID)
    {
        $this->ACTSTATID = $ACTSTATID;
    }

    public function getActstatid()
    {
        return $this->ACTSTATID;
    }

    public function setName($NAME)
    {
        $this->name = $NAME;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTestDate(DateTimeInterface $testDate)
    {
        $this->testDate = $testDate;
    }

    public function getTestDate()
    {
        return $this->testDate;
    }
}
