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
     * Проверяет, что объект выбросит исключение, если привязки дублируются.
     */
    public function testConstructorDoublingBindingsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $registry = $this->createRegistry(null, ['test' => 'test', 'test1' => 'test']);
    }

    /**
     * Проверяет, что объект верно обработает исключение от парсера.
     */
    public function testParserException()
    {
        $registry = $this->createRegistry(__DIR__ . '/_fixtures/testParserException.yaml');

        $this->expectException(EntityRegistryException::class);
        $registry->hasEntityDescriptor('empty');
    }

    /**
     * Проверяет, что объект верно обработает исключение при создании дескриптора.
     */
    public function testBuildingException()
    {
        $registry = $this->createRegistry(__DIR__ . '/_fixtures/testBuildingException.yaml');

        $this->expectException(EntityRegistryException::class);
        $registry->hasEntityDescriptor('empty');
    }

    /**
     * Проверяет, что объект верно проверяет существование описания сущности по ее имени.
     */
    public function testHasEntityDescriptor()
    {
        $registry = $this->createRegistry();

        $this->assertFalse($registry->hasEntityDescriptor('empty'));
        $this->assertTrue($registry->hasEntityDescriptor('   NormativeDocumenttype'));
    }

    /**
     * Проверяет, что объект вернет описание сущности по ее имени.
     */
    public function testGetEntityDescriptor()
    {
        $name = 'NormativeDocumentType';
        $rawName = '   NormativeDocumentTYpe ';
        $fieldName = 'NAME';

        $registry = $this->createRegistry();
        $descriptor = $registry->getEntityDescriptor($rawName);

        $this->assertSame($name, $descriptor->getName());
        $this->assertSame($fieldName, $descriptor->getField($fieldName)->getName());
    }

    /**
     * Проверяет, что объект вернет исключение, если сущности с заданным именем не сществует
     */
    public function testGetEntityDescriptorException()
    {
        $registry = $this->createRegistry();

        $this->expectException(InvalidArgumentException::class);
        $descriptor = $registry->getEntityDescriptor('empty');
    }

    /**
     * Проверяет, что объект вернет дескриптор по имени класса из массива биндингов.
     */
    public function testGetDescriptorForClass()
    {
        $className = 'TestClass';
        $entityName = 'NormativeDocumentType';

        $registry = $this->createRegistry(null, [
            $className => $entityName,
        ]);
        $descriptor = $registry->getDescriptorForClass($className);

        $this->assertSame($entityName, $descriptor->getName());
    }

    /**
     * Проверяет, что объект вернет дескриптор по имени класса из массива биндингов.
     */
    public function testGetDescriptorForClassException()
    {
        $registry = $this->createRegistry();

        $this->expectException(InvalidArgumentException::class);
        $descriptor = $registry->getDescriptorForClass('empty');
    }

    /**
     * Создает объект.
     *
     * @param string|null $fileName
     * @param array|null  $bindings
     *
     * @return YamlEntityRegistry
     */
    protected function createRegistry(?string $fileName = null, ?array $bindings = null): YamlEntityRegistry
    {
        $fileName = $fileName ?: __DIR__ . '/_fixtures/test.yaml';
        $bindings = $bindings ?: ['Test' => 'NormativeDocumentType'];

        return new YamlEntityRegistry($fileName, $bindings);
    }
}
