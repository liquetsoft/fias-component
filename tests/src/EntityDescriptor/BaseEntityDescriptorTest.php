<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityDescriptor;

use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\EntityDescriptor\BaseEntityDescriptor;
use InvalidArgumentException;

/**
 * Тест для объекта, который хранит описание сущности во внутреннем массиве.
 */
class BaseEntityDescriptorTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно вернет имя сущности.
     */
    public function testGetName()
    {
        $name = $this->createFakeData()->word;

        $descriptor = new BaseEntityDescriptor([
            'name' => $name,
            'xmlPath' => '/test/item',
        ]);

        $this->assertSame($name, $descriptor->getName());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если имя не задано.
     */
    public function testEmptyNameException()
    {
        $this->expectException(InvalidArgumentException::class);
        $descriptor = new BaseEntityDescriptor([
            'xmlPath' => '/test/item',
        ]);
    }

    /**
     * Проверяет, что объект правильно вернет xpath сущности в файле.
     */
    public function testGetXmlPath()
    {
        $xpath = '/root/' . $this->createFakeData()->word;

        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => $xpath,
        ]);

        $this->assertSame($xpath, $descriptor->getXmlPath());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если xpath не задан.
     */
    public function testEmptyXmlPathException()
    {
        $this->expectException(InvalidArgumentException::class);
        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
        ]);
    }

    /**
     * Проверяет, что объект правильно вернет описание сущности.
     */
    public function testGetDescription()
    {
        $description = $this->createFakeData()->text;

        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => '/test/item',
            'description' => $description,
        ]);

        $this->assertSame($description, $descriptor->getDescription());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустое описание.
     */
    public function testGetDescriptionDefault()
    {
        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => '/test/item',
        ]);

        $this->assertSame('', $descriptor->getDescription());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для загрузки данных.
     */
    public function testGetXmlInsertFileMask()
    {
        $file = $this->createFakeData()->word . '_*.xml';

        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => '/test/item',
            'insertFileMask' => $file,
        ]);

        $this->assertSame($file, $descriptor->getXmlInsertFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для загрузки данных.
     */
    public function testGetXmlInsertFileMaskDefault()
    {
        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => '/test/item',
        ]);

        $this->assertSame('', $descriptor->getXmlInsertFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для удаления данных.
     */
    public function testGetXmlDeleteFileMask()
    {
        $file = $this->createFakeData()->word . '_*.xml';

        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => '/test/item',
            'deleteFileMask' => $file,
        ]);

        $this->assertSame($file, $descriptor->getXmlDeleteFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для удаления данных.
     */
    public function testGetXmlDeleteFileMaskDefault()
    {
        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => '/test/item',
        ]);

        $this->assertSame('', $descriptor->getXmlDeleteFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет количество частей, на которые
     * нужно разбить таблицу с данной сущностью.
     */
    public function testGetPartitionsCount()
    {
        $count = (string) $this->createFakeData()->numberBetween(1, 10);

        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => '/test/item',
            'partitionsCount' => $count,
        ]);

        $this->assertSame((int) $count, $descriptor->getPartitionsCount());
    }

    /**
     * Проверяет, что объект по умолчанию вернет одну часть для таблицы.
     */
    public function testGetPartitionsCountDefault()
    {
        $descriptor = new BaseEntityDescriptor([
            'name' => 'Test',
            'xmlPath' => '/test/item',
        ]);

        $this->assertSame(1, $descriptor->getPartitionsCount());
    }
}
