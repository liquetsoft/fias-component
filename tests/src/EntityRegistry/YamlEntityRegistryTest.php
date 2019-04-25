<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityDescriptor;

use Liquetsoft\Fias\Component\EntityRegistry\YamlEntityRegistry;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use InvalidArgumentException;

/**
 * Тест для объекта, который получает описания сущностей из yaml.
 */
class YamlEntityRegistryTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если yaml не существует.
     */
    public function testConstructorUnexistedException()
    {
        $this->expectException(InvalidArgumentException::class);
        $registry = $this->createRegistry(__DIR__ . '/_fixtures/empty.yaml');
    }

    /**
     * Проверяет, что объект верно обработает исключение от парсера.
     */
    public function testParserException()
    {
        $registry = $this->createRegistry(__DIR__ . '/_fixtures/testParserException.yaml');

        $this->expectException(EntityRegistryException::class);
        $registry->hasDescriptor('empty');
    }

    /**
     * Проверяет, что объект верно обработает исключение при создании дескриптора.
     */
    public function testBuildingException()
    {
        $registry = $this->createRegistry(__DIR__ . '/_fixtures/testBuildingException.yaml');

        $this->expectException(EntityRegistryException::class);
        $registry->hasDescriptor('empty');
    }

    /**
     * Проверяет, что объект возвращает массив всех дескрипторов.
     */
    public function testGetDescriptiors()
    {
        $registry = $this->createRegistry();
        $descriptors = $registry->getDescriptors();

        $this->assertCount(2, $descriptors);
        $this->assertSame('IntervalStatus', $descriptors[0]->getName());
        $this->assertSame('NormativeDocumentType', $descriptors[1]->getName());
    }

    /**
     * Проверяет, что объект верно проверяет существование описания сущности по ее имени.
     */
    public function testHasDescriptor()
    {
        $registry = $this->createRegistry();

        $this->assertFalse($registry->hasDescriptor('empty'));
        $this->assertTrue($registry->hasDescriptor('   NormativeDocumenttype'));
    }

    /**
     * Проверяет, что объект вернет описание сущности по ее имени.
     */
    public function testGetDescriptor()
    {
        $name = 'NormativeDocumentType';
        $rawName = '   NormativeDocumentTYpe ';
        $fieldName = 'NAME';

        $registry = $this->createRegistry();
        $descriptor = $registry->getDescriptor($rawName);

        $this->assertSame($name, $descriptor->getName());
        $this->assertSame($fieldName, $descriptor->getField($fieldName)->getName());
    }

    /**
     * Проверяет, что объект вернет исключение, если сущности с заданным именем не сществует
     */
    public function testGetDescriptorException()
    {
        $registry = $this->createRegistry();

        $this->expectException(InvalidArgumentException::class);
        $descriptor = $registry->getDescriptor('empty');
    }

    /**
     * Создает объект.
     *
     * @param string|null $fileName
     *
     * @return YamlEntityRegistry
     */
    protected function createRegistry(?string $fileName = null): YamlEntityRegistry
    {
        $fileName = $fileName ?: __DIR__ . '/_fixtures/test.yaml';

        return new YamlEntityRegistry($fileName);
    }
}
