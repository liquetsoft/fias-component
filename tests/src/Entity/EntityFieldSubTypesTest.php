<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityField;

use Liquetsoft\Fias\Component\Entity\EntityFieldSubTypes;
use Liquetsoft\Fias\Component\Entity\EntityFieldTypes;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для методов списка подтипов поля.
 *
 * @internal
 */
class EntityFieldSubTypesTest extends BaseCase
{
    /**
     * Проверяет, что подтип вернет правильный базовый тип.
     */
    public function testgetBaseType(): void
    {
        $this->assertSame(EntityFieldTypes::STRING, EntityFieldSubTypes::DATE->getBaseType());
        $this->assertSame(EntityFieldTypes::STRING, EntityFieldSubTypes::UUID->getBaseType());
        $this->assertNull(EntityFieldSubTypes::NONE->getBaseType());
    }
}
