<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasEntity;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntityFieldSubType;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityFieldType;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для методов списка подтипов поля.
 *
 * @internal
 */
class FiasEntityFieldSubTypeTest extends BaseCase
{
    /**
     * Проверяет, что подтип вернет правильный базовый тип.
     */
    public function testgetBaseType(): void
    {
        $this->assertSame(FiasEntityFieldType::STRING, FiasEntityFieldSubType::DATE->getBaseType());
        $this->assertSame(FiasEntityFieldType::STRING, FiasEntityFieldSubType::UUID->getBaseType());
        $this->assertNull(FiasEntityFieldSubType::NONE->getBaseType());
    }
}
