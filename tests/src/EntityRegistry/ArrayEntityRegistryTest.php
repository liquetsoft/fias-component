<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityRegistry;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityRegistry\ArrayEntityRegistry;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который получает описания сущностей из yaml.
 */
class ArrayEntityRegistryTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если задан объект,
     * который не реализует EntityDescriptor.
     */
    public function testConstructorWrongInstanceException()
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();

        $this->expectException(InvalidArgumentException::class);
        new ArrayEntityRegistry(
            [
                $descriptor,
                'test',
            ]
        );
    }

    /**
     * Проверяет, что объект возвращает массив всех дескрипторов.
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptors()
    {
        $descriptors = [
            'test' => $this->getMockBuilder(EntityDescriptor::class)->getMock(),
            $this->getMockBuilder(EntityDescriptor::class)->getMock(),
        ];

        $registry = new ArrayEntityRegistry($descriptors);
        $descriptorsTest = $registry->getDescriptors();

        $this->assertSame(array_values($descriptors), $descriptorsTest);
    }

    /**
     * Проверяет, что объект верно проверяет существование описания сущности по ее имени.
     *
     * @throws EntityRegistryException
     */
    public function testHasDescriptor()
    {
        $name = 'Test';

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue($name));

        $registry = new ArrayEntityRegistry([$descriptor]);

        $this->assertFalse($registry->hasDescriptor('empty'));
        $this->assertTrue($registry->hasDescriptor(" {$name}   "));
    }

    /**
     * Проверяет, что объект вернет описание сущности по ее имени.
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptor()
    {
        $name = 'Test';

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue($name));

        $registry = new ArrayEntityRegistry([$descriptor]);

        $this->assertSame($descriptor, $registry->getDescriptor(" {$name}   "));
    }

    /**
     * Проверяет, что объект вернет исключение, если сущности с заданным именем не существует
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptorException()
    {
        $registry = new ArrayEntityRegistry([]);

        $this->expectException(InvalidArgumentException::class);
        $registry->getDescriptor('empty');
    }
}
