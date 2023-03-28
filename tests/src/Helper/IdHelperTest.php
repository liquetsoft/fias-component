<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Helper;

use Liquetsoft\Fias\Component\Helper\IdHelper;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для класса, который содержит методы для генерации уникальных идентификаторов.
 *
 * @internal
 */
class IdHelperTest extends BaseCase
{
    /**
     * Проверяет, что метод вернет уникальный идентификатор.
     */
    public function testCreateUniqueId(): void
    {
        $id = IdHelper::createUniqueId();

        $this->assertSame(32, \strlen($id));
    }

    /**
     * Проверяет, что метод вернет идентификатор компонента.
     */
    public function testGetComponentId(): void
    {
        $id = IdHelper::getComponentId();

        $this->assertSame('fias_component', $id);
    }
}