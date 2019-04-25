<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityDescriptor;

use Liquetsoft\Fias\Component\EntityManager\BaseEntityManager;
use Liquetsoft\Fias\Component\EntityRegistry\EntityRegistry;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use InvalidArgumentException;

/**
 * Тест для объекта, содержит соответствия между сущностями ФИАС и их реализациями.
 */
class BaseEntityManagerTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если реализация не указана.
     */
    public function testConstructNoClassException()
    {
        $registry = $this->getMockBuilder(EntityRegistry::class)->getMock();

        $this->expectException(InvalidArgumentException::class);
        $manager = new BaseEntityManager($registry, [
            'TestEntity' => 'TestClass',
            'TestEntity1' => '\\',
        ]);
    }

    /**
     * Проверяет, что объект возвращает дескриптор по имени сущности.
     */
    public function testGetDescriptorByEntityName()
    {
        $class = 'TestClass';
        $entityName = 'TestEntity';

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';

        $entityName2 = 'TestEntity2';

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();

        $registry = $this->getMockBuilder(EntityRegistry::class)->getMock();
        $registry->method('hasDescriptor')->will($this->returnCallback(function ($name) use ($entityName, $entityName2) {
            return $name === $entityName || $name === $entityName2;
        }));
        $registry->method('getDescriptor')->will($this->returnCallback(function ($name) use ($entityName, $entityName2, $descriptor) {
            return $name === $entityName || $name === $entityName2 ? $descriptor : null;
        }));

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            $entityName1 => $class1,
        ]);

        $this->assertSame($descriptor, $manager->getDescriptorByEntityName($entityName));
        $this->assertNull($manager->getDescriptorByEntityName($entityName1));
        $this->assertNull($manager->getDescriptorByEntityName($entityName2));
        $this->assertNull($manager->getDescriptorByEntityName('empty'));
    }

    /**
     * Проверяет, что объект возвращает класс реализации для указанного дескриптора.
     */
    public function testGetClassByDescriptor()
    {
        $class = 'TestClass';
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue($entityName));

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->will($this->returnValue($entityName1));

        $registry = $this->getMockBuilder(EntityRegistry::class)->getMock();
        $registry->method('hasDescriptor')->will($this->returnCallback(function ($name) use ($entityName, $entityName1) {
            return $name === $entityName || $name === $entityName1;
        }));

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            $entityName1 => $class1,
        ]);

        $this->assertSame($class, $manager->getClassByDescriptor($descriptor));
        $this->assertSame($class1, $manager->getClassByDescriptor($descriptor1));
        $this->assertNull($manager->getDescriptorByEntityName('empty'));
    }
}
