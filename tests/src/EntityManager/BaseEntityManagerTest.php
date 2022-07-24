<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityManager;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\BaseEntityManager;
use Liquetsoft\Fias\Component\EntityRegistry\ArrayEntityRegistry;
use Liquetsoft\Fias\Component\EntityRegistry\EntityRegistry;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * Тест для объекта, содержит соответствия между сущностями ФИАС и их реализациями.
 *
 * @internal
 */
class BaseEntityManagerTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если реализация не указана.
     */
    public function testConstructNoClassException(): void
    {
        /** @var MockObject&EntityRegistry */
        $registry = $this->getMockBuilder(EntityRegistry::class)->getMock();

        $this->expectException(InvalidArgumentException::class);
        new BaseEntityManager(
            $registry,
            [
                'TestEntity' => 'TestClass',
                'TestEntity1' => '\\',
            ]
        );
    }

    /**
     * Проверяет, что объект возвращает дескриптор по имени сущности.
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptorByEntityName(): void
    {
        $class = 'TestClass';
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->willReturn($entityName);

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
    public function testGetClassByDescriptor(): void
    {
        $class = 'TestClass';
        $entityName = 'TestEntity';

        /** @var MockObject&EntityDescriptor */
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->willReturn($entityName);

        /** @var MockObject&EntityDescriptor */
        $descriptorNotBound = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptorNotBound->method('getName')->willReturn('not_bound');

        $registry = new ArrayEntityRegistry([$descriptor]);

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            'TestEntity1' => 'TestClass1',
        ]);

        $this->assertSame($class, $manager->getClassByDescriptor($descriptor));
        $this->assertNull($manager->getClassByDescriptor($descriptorNotBound));
    }

    /**
     * Проверят, что объект правильно возвращает дескриптор для сущности,
     * к которой относится файл для импорта.
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptorByInsertFile(): void
    {
        $file = 'insert_xml.xml';

        $class = 'TestClass';
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->willReturn($entityName);
        $descriptor->method('isFileNameFitsXmlInsertFileMask')->willReturn(false);

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->willReturn($entityName1);
        $descriptor1->method('isFileNameFitsXmlInsertFileMask')->with($this->equalTo($file))->willReturn(true);

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
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptorByDeleteFile(): void
    {
        $file = 'insert_xml.xml';

        $class = 'TestClass';
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->willReturn($entityName);
        $descriptor->method('isFileNameFitsXmlDeleteFileMask')->willReturn(false);

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->willReturn($entityName1);
        $descriptor1->method('isFileNameFitsXmlDeleteFileMask')->willReturnCallback(
            function (string $testFile) use ($file) {
                return $testFile === $file;
            }
        );

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
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptorByClass(): void
    {
        $class = 'TestClass';
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->willReturn($entityName);

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->willReturn($entityName1);

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
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptorByObject(): void
    {
        $class = stdClass::class;
        $entityName = 'TestEntity';
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->willReturn($entityName);

        $class1 = 'TestClass1';
        $entityName1 = 'TestEntity1';
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->willReturn($entityName1);

        $registry = new ArrayEntityRegistry([$descriptor, $descriptor1]);

        $manager = new BaseEntityManager($registry, [
            $entityName => $class,
            $entityName1 => $class1,
        ]);

        $this->assertSame($descriptor, $manager->getDescriptorByObject(new stdClass()));
        $this->assertNull($manager->getDescriptorByObject('TestEmpty'));
    }

    /**
     * Проверяет, что объект вернет список всех классов, которые имеют отношения
     * к сущностям ФИАС.
     */
    public function testGetBoundClasses(): void
    {
        /** @var MockObject&EntityRegistry */
        $registry = $this->getMockBuilder(EntityRegistry::class)->getMock();

        $manager = new BaseEntityManager(
            $registry,
            [
                'TestEntity1' => '\Test\Class1',
                'TestEntity2' => 'Test\Class2',
            ]
        );

        $this->assertSame(
            [
                'Test\Class1',
                'Test\Class2',
            ],
            $manager->getBindedClasses()
        );
    }
}
