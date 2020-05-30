<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityDescriptor;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\BaseEntityDescriptor;
use Liquetsoft\Fias\Component\EntityField\EntityField;
use Liquetsoft\Fias\Component\Tests\BaseCase;

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

        $descriptor = $this->createDescriptor([
            'name' => $name,
        ]);

        $this->assertSame($name, $descriptor->getName());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если имя не задано.
     */
    public function testEmptyNameException()
    {
        $this->expectException(InvalidArgumentException::class);
        $descriptor = $this->createDescriptor([
            'name' => null,
        ]);
    }

    /**
     * Проверяет, что объект правильно вернет xpath сущности в файле.
     */
    public function testGetXmlPath()
    {
        $xpath = '/root/' . $this->createFakeData()->word;

        $descriptor = $this->createDescriptor([
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
        $descriptor = $this->createDescriptor([
            'xmlPath' => null,
        ]);
    }

    /**
     * Проверяет, что объект правильно вернет описание сущности.
     */
    public function testGetDescription()
    {
        $description = $this->createFakeData()->text;

        $descriptor = $this->createDescriptor([
            'description' => $description,
        ]);

        $this->assertSame($description, $descriptor->getDescription());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустое описание.
     */
    public function testGetDescriptionDefault()
    {
        $descriptor = $this->createDescriptor();

        $this->assertSame('', $descriptor->getDescription());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для загрузки данных.
     */
    public function testGetInsertFileMask()
    {
        $file = $this->createFakeData()->word . '_*.xml';

        $descriptor = $this->createDescriptor([
            'insertFileMask' => $file,
        ]);

        $this->assertSame($file, $descriptor->getInsertFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для загрузки данных.
     */
    public function testGetInsertFileMaskDefault()
    {
        $descriptor = $this->createDescriptor();

        $this->assertSame('', $descriptor->getInsertFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для удаления данных.
     */
    public function testGetDeleteFileMask()
    {
        $file = $this->createFakeData()->word . '_*.xml';

        $descriptor = $this->createDescriptor([
            'deleteFileMask' => $file,
        ]);

        $this->assertSame($file, $descriptor->getDeleteFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для удаления данных.
     */
    public function testGetDeleteFileMaskDefault()
    {
        $descriptor = $this->createDescriptor();

        $this->assertSame('', $descriptor->getDeleteFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет количество частей, на которые
     * нужно разбить таблицу с данной сущностью.
     */
    public function testGetPartitionsCount()
    {
        $count = (string) $this->createFakeData()->numberBetween(1, 10);

        $descriptor = $this->createDescriptor([
            'partitionsCount' => $count,
        ]);

        $this->assertSame((int) $count, $descriptor->getPartitionsCount());
    }

    /**
     * Проверяет, что объект по умолчанию вернет одну часть для таблицы.
     */
    public function testGetPartitionsCountDefault()
    {
        $descriptor = $this->createDescriptor();

        $this->assertSame(1, $descriptor->getPartitionsCount());
    }

    /**
     * Проверяет, что объект вернет список своих полей.
     */
    public function testGetFields()
    {
        $field1 = $this->getMockBuilder(EntityField::class)->getMock();
        $field1->method('getName')->will($this->returnValue('test1'));

        $field2 = $this->getMockBuilder(EntityField::class)->getMock();
        $field2->method('getName')->will($this->returnValue('test2'));

        $fields = [$field1, $field2];

        $descriptor = $this->createDescriptor([
            'fields' => $fields,
        ]);

        $this->assertSame($fields, $descriptor->getFields());
    }

    /**
     * Проверяет, что объект проверяет наличие поля с указанным именем.
     */
    public function testHasField()
    {
        $field = $this->getMockBuilder(EntityField::class)->getMock();
        $field->method('getName')->will($this->returnValue('test1'));

        $descriptor = $this->createDescriptor([
            'fields' => ['test' => $field],
        ]);

        $this->assertTrue($descriptor->hasField('test1'));
        $this->assertFalse($descriptor->hasField('test'));
    }

    /**
     * Проверяет, что объект вернет поле по указанному имени.
     */
    public function testGetField()
    {
        $field = $this->getMockBuilder(EntityField::class)->getMock();
        $field->method('getName')->will($this->returnValue('test1'));

        $descriptor = $this->createDescriptor([
            'fields' => ['test' => $field],
        ]);

        $this->assertSame($field, $descriptor->getField('test1'));
    }

    /**
     * Проверяет, что объект выбросит исключение, если не найдет поле по имени.
     */
    public function testGetFieldException()
    {
        $field = $this->getMockBuilder(EntityField::class)->getMock();
        $field->method('getName')->will($this->returnValue('test1'));

        $descriptor = $this->createDescriptor([
            'fields' => ['test' => $field],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $field = $descriptor->getField('test2');
    }

    /**
     * Проверяет, что объект выбросит исключение, если поля не были заданы.
     */
    public function testEmptyFieldsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $descriptor = $this->createDescriptor([
            'fields' => null,
        ]);
    }

    /**
     * Проверяет, что объект выбросит исключение, если указано неверное поле.
     */
    public function testWrongFieldTypeException()
    {
        $field1 = $this->getMockBuilder(EntityField::class)->getMock();
        $field2 = 123;
        $fields = [$field1, $field2];

        $this->expectException(InvalidArgumentException::class);
        $descriptor = $this->createDescriptor([
            'fields' => $fields,
        ]);
    }

    /**
     * Проверяет, что объект выбросит исключение, если имена полей дублируются.
     */
    public function testDoublingFieldsNamesException()
    {
        $field1 = $this->getMockBuilder(EntityField::class)->getMock();
        $field1->method('getName')->will($this->returnValue('test'));

        $field2 = $this->getMockBuilder(EntityField::class)->getMock();
        $field2->method('getName')->will($this->returnValue('test'));

        $this->expectException(InvalidArgumentException::class);
        $descriptor = $this->createDescriptor([
            'fields' => [$field1, $field2],
        ]);
    }

    /**
     * Проверяет, что объект правильно сопоставляет имена файлов для вставки с шаблоном.
     */
    public function testIsFileNameMatchInsertFileMask()
    {
        $fileMask = '*_test_*.xml';

        $descriptor = $this->createDescriptor([
            'insertFileMask' => $fileMask,
        ]);

        $this->assertTrue($descriptor->isFileNameMatchInsertFileMask('123_test_321.xml'));
        $this->assertFalse($descriptor->isFileNameMatchInsertFileMask('123_321_test.xml'));
    }

    /**
     * Проверяет, что объект правильно сопоставляет имена файлов для удаления с шаблоном.
     */
    public function testIsFileNameMatchDeleteFileMask()
    {
        $fileMask = '*_test_*.xml';

        $descriptor = $this->createDescriptor([
            'deleteFileMask' => $fileMask,
        ]);

        $this->assertTrue($descriptor->isFileNameMatchDeleteFileMask('123_test_321.xml'));
        $this->assertFalse($descriptor->isFileNameMatchDeleteFileMask('123_321_test.xml'));
    }

    /**
     * Создает объект по умолчанию.
     *
     * @param array $options
     *
     * @return BaseEntityDescriptor
     */
    protected function createDescriptor(array $options = []): BaseEntityDescriptor
    {
        $field = $this->getMockBuilder(EntityField::class)->getMock();
        $field->method('getName')->will($this->returnValue('test'));

        $resultOptions = array_merge([
            'name' => 'Test',
            'xmlPath' => '/test/item',
            'fields' => [$field],
        ], $options);

        return new BaseEntityDescriptor($resultOptions);
    }
}
