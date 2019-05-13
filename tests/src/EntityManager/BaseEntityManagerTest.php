<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityDescriptor;

use Liquetsoft\Fias\Component\EntityManager\BaseEntityManager;
use Liquetsoft\Fias\Component\EntityRegistry\ArrayEntityRegistry;
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
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue($entityName));

        $registry = new ArrayEntityRegistry([$descriptor]);

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            'TestEntity1' => 'TestClass1',
        ]);

        $this->assertSame($descriptor, $manager->getDescriptorByEntityName($entityName));
        $this->assertNull($manager->getDescriptorByEntityName('TestEntity1'));
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

        $descriptorNotBinded = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptorNotBinded->method('getName')->will($this->returnValue('not_binded'));

        $registry = new ArrayEntityRegistry([$descriptor]);

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            'TestEntity1' => 'TestClass1',
        ]);

        $this->assertSame($class, $manager->getClassByDescriptor($descriptor));
        $this->assertNull($manager->getClassByDescriptor($descriptorNotBinded));
    }

    /**
     * Проверят, что объект правильно возвращает дескриптор для сущности,
     * к которой относится файл для импорта.
     */
    public function testGetDescriptorByInsertFile()
    {
        $file = 'insert_xml.xml';

        $class = 'TestClass';
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue($entityName));
        $descriptor->method('isFileNameFitsXmlInsertFileMask')->will($this->returnValue(false));

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->will($this->returnValue($entityName1));
        $descriptor1->method('isFileNameFitsXmlInsertFileMask')->with($this->equalTo($file))->will($this->returnValue(true));

        $registry = new ArrayEntityRegistry([$descriptor, $descriptor1]);

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            $entityName1 => $class1,
        ]);

        $this->assertSame($descriptor1, $manager->getDescriptorByInsertFile($file));
        $this->assertNull($manager->getDescriptorByDeleteFile('123.xml'));
    }

    /**
     * Проверят, что объект правильно возвращает дескриптор для сущности,
     * к которой относится файл для удаления.
     */
    public function testGetDescriptorByDeleteFile()
    {
        $file = 'insert_xml.xml';

        $class = 'TestClass';
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue($entityName));
        $descriptor->method('isFileNameFitsXmlDeleteFileMask')->will($this->returnValue(false));

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->will($this->returnValue($entityName1));
        $descriptor1->method('isFileNameFitsXmlDeleteFileMask')->will($this->returnCallback(function ($testFile) use ($file) {
            return $testFile === $file;
        }));

        $registry = new ArrayEntityRegistry([$descriptor, $descriptor1]);

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            $entityName1 => $class1,
        ]);

        $this->assertSame($descriptor1, $manager->getDescriptorByDeleteFile($file));
        $this->assertNull($manager->getDescriptorByDeleteFile('123.xml'));
    }

    /**
     * Проверят, что объект правильно возвращает дескриптор по классу сущности.
     */
    public function testGetDescriptorByClass()
    {
        $class = 'TestClass';
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue($entityName));

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->will($this->returnValue($entityName1));

        $registry = new ArrayEntityRegistry([$descriptor, $descriptor1]);

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            $entityName1 => $class1,
        ]);

        $this->assertSame($descriptor1, $manager->getDescriptorByClass($class1));
        $this->assertNull($manager->getDescriptorByClass('TestEmpty'));
    }

    /**
     * Проверят, что объект правильно возвращает дескриптор по объекту.
     */
    public function testGetDescriptorByObject()
    {
        $class = \stdClass::class;
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue($entityName));

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->will($this->returnValue($entityName1));

        $registry = new ArrayEntityRegistry([$descriptor, $descriptor1]);

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            $entityName1 => $class1,
        ]);

        $this->assertSame($descriptor, $manager->getDescriptorByObject(new \stdClass));
        $this->assertNull($manager->getDescriptorByObject('TestEmpty'));
    }

    /**
     * Проверяет, что объект вернет список всехх лкассов, которые имеют отношения
     * к сущностям ФИАС.
     */
    public function testGetBindedClasses()
    {
        $registry = $this->getMockBuilder(EntityRegistry::class)->getMock();

        $manager = new BaseEntityManager($registry, [
            'TestEntity1' => '\Test\Class1',
            'TestEntity2' => 'Test\Class2',
        ]);

        $this->assertSame(['Test\Class1', 'Test\Class2'], $manager->getBindedClasses());
    }
}
