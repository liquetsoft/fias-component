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
        $this->assertTrue($registry->hasEntityDescriptor('NormativeDocumentType'));
    }

    /**
     * Проверяет, что объект вернет описание сущности по ее имени.
     */
    public function testGetEntityDescriptor()
    {
        $name = 'NormativeDocumentType';
        $fieldName = 'NAME';

        $registry = $this->createRegistry();
        $descriptor = $registry->getEntityDescriptor($name);

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
     * Создает объект.
     *
     * @param string $fileName
     *
     * @return YamlEntityRegistry
     */
    protected function createRegistry(?string $fileName = null): YamlEntityRegistry
    {
        $fileName = $fileName ?: __DIR__ . '/_fixtures/test.yaml';

        return new YamlEntityRegistry($fileName);
    }
}
