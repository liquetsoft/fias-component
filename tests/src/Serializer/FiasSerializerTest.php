<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Serializer;

use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Tests\BaseCase;

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
        $data = '<ActualStatus ACTSTATID="2" NAME="Не актуальный"/>';
        $serializer = new FiasSerializer;

        $object = $serializer->deserialize($data, FiasSerializerObject::class, 'xml');

        $this->assertInstanceOf(FiasSerializerObject::class, $object);
        $this->assertSame(2, $object->getActstatid());
        $this->assertSame('Не актуальный', $object->getName());
    }
}


/**
 * Мок для проверки сериализатора.
 */
class FiasSerializerObject
{
    private $ACTSTATID;
    private $name;

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
}
