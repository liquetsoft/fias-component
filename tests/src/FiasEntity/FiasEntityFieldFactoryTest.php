<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasEntity;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntityFieldFactory;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityFieldSubType;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityFieldType;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для фабрики, которая создает поля сущностей.
 *
 * @internal
 */
class FiasEntityFieldFactoryTest extends BaseCase
{
    /**
     * Проверяет, что объект создаст поле из массива значений.
     */
    public function testCreateFromArray(): void
    {
        $array = [
            'type' => 'string',
            'subType' => 'date',
            'name' => ' name ',
            'description' => 123,
            'length' => '123',
            'isNullable' => true,
            'isPrimary' => true,
            'isIndex' => 0,
        ];

        $field = FiasEntityFieldFactory::createFromArray($array);

        $this->assertSame(FiasEntityFieldType::STRING, $field->getType());
        $this->assertSame(FiasEntityFieldSubType::DATE, $field->getSubType());
        $this->assertSame(trim($array['name']), $field->getName());
        $this->assertSame((string) $array['description'], $field->getDescription());
        $this->assertSame((int) $array['length'], $field->getLength());
        $this->assertSame($array['isNullable'], $field->isNullable());
        $this->assertSame($array['isPrimary'], $field->isPrimary());
        $this->assertSame((bool) $array['isIndex'], $field->isIndex());
        $this->assertFalse($field->isPartition());
    }
}
