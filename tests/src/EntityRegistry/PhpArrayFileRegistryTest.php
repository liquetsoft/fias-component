<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityRegistry;

use Liquetsoft\Fias\Component\EntityRegistry\PhpArrayFileRegistry;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который получает описания сущностей из php файла с массивом.
 *
 * @internal
 */
class PhpArrayFileRegistryTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если файл не существует.
     */
    public function testConstructorNonExistedException(): void
    {
        $registry = $this->createRegistry(__DIR__ . '/_fixtures/notExist.php');

        $this->expectException(EntityRegistryException::class);
        $registry->getDescriptors();
    }

    /**
     * Проверяет, что объект выбросит исключение, если файл имеет неправильное расширение.
     */
    public function testConstructorBadExtensionException(): void
    {
        $registry = $this->createRegistry(__DIR__ . '/_fixtures/badExtension.yaml');

        $this->expectException(EntityRegistryException::class);
        $registry->getDescriptors();
    }

    /**
     * Проверяет, что объект верно обработает исключение при создании дескриптора.
     */
    public function testBuildingException(): void
    {
        $registry = $this->createRegistry(__DIR__ . '/_fixtures/testBuildingException.php');

        $this->expectException(EntityRegistryException::class);
        $registry->hasDescriptor('empty');
    }

    /**
     * Проверяет, что объект возвращает массив всех дескрипторов.
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptors(): void
    {
        $registry = $this->createRegistry();
        $descriptors = $registry->getDescriptors();

        $this->assertCount(2, $descriptors);
        $this->assertSame('IntervalStatus', $descriptors[0]->getName());
        $this->assertSame('NormativeDocumentType', $descriptors[1]->getName());
    }

    /**
     * Проверяет, что объект верно проверяет существование описания сущности по ее имени.
     *
     * @throws EntityRegistryException
     */
    public function testHasDescriptor(): void
    {
        $registry = $this->createRegistry();

        $this->assertFalse($registry->hasDescriptor('empty'));
        $this->assertTrue($registry->hasDescriptor('   NormativeDocumenttype'));
    }

    /**
     * Проверяет, что объект вернет описание сущности по ее имени.
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptor(): void
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
     * Проверяет, что объект вернет исключение, если сущности с заданным именем не существует.
     *
     * @throws EntityRegistryException
     */
    public function testGetDescriptorException(): void
    {
        $registry = $this->createRegistry();

        $this->expectException(\InvalidArgumentException::class);
        $registry->getDescriptor('empty');
    }

    /**
     * Создает объект.
     */
    protected function createRegistry(?string $fileName = null): PhpArrayFileRegistry
    {
        $fileName = $fileName ?: __DIR__ . '/_fixtures/registryTest.php';

        return new PhpArrayFileRegistry($fileName);
    }
}
