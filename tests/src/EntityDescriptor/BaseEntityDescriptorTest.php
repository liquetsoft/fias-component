<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityDescriptor;

use Liquetsoft\Fias\Component\EntityDescriptor\BaseEntityDescriptor;
use Liquetsoft\Fias\Component\EntityField\EntityField;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит описание сущности во внутреннем массиве.
 *
 * @internal
 */
class BaseEntityDescriptorTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно вернет имя сущности.
     */
    public function testGetName(): void
    {
        $name = $this->createFakeData()->word();

        $descriptor = $this->createDescriptor([
            'name' => $name,
        ]);

        $this->assertSame($name, $descriptor->getName());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если имя не задано.
     */
    public function testEmptyNameException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createDescriptor(
            [
                'name' => null,
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет xpath сущности в файле.
     */
    public function testGetXmlPath(): void
    {
        $xpath = '/root/' . $this->createFakeData()->word();

        $descriptor = $this->createDescriptor([
            'xmlPath' => $xpath,
        ]);

        $this->assertSame($xpath, $descriptor->getXmlPath());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если xpath не задан.
     */
    public function testEmptyXmlPathException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createDescriptor(
            [
                'xmlPath' => null,
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет описание сущности.
     */
    public function testGetDescription(): void
    {
        $description = $this->createFakeData()->text();

        $descriptor = $this->createDescriptor([
            'description' => $description,
        ]);

        $this->assertSame($description, $descriptor->getDescription());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустое описание.
     */
    public function testGetDescriptionDefault(): void
    {
        $descriptor = $this->createDescriptor();

        $this->assertSame('', $descriptor->getDescription());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для загрузки данных.
     */
    public function testGetXmlInsertFileMask(): void
    {
        $file = $this->createFakeData()->word() . '_*.xml';

        $descriptor = $this->createDescriptor([
            'insertFileMask' => $file,
        ]);

        $this->assertSame($file, $descriptor->getXmlInsertFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для загрузки данных.
     */
    public function testGetXmlInsertFileMaskDefault(): void
    {
        $descriptor = $this->createDescriptor();

        $this->assertSame('', $descriptor->getXmlInsertFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для удаления данных.
     */
    public function testGetXmlDeleteFileMask(): void
    {
        $file = $this->createFakeData()->word() . '_*.xml';

        $descriptor = $this->createDescriptor([
            'deleteFileMask' => $file,
        ]);

        $this->assertSame($file, $descriptor->getXmlDeleteFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для удаления данных.
     */
    public function testGetXmlDeleteFileMaskDefault(): void
    {
        $descriptor = $this->createDescriptor();

        $this->assertSame('', $descriptor->getXmlDeleteFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет количество частей, на которые
     * нужно разбить таблицу с данной сущностью.
     */
    public function testGetPartitionsCount(): void
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
    public function testGetPartitionsCountDefault(): void
    {
        $descriptor = $this->createDescriptor();

        $this->assertSame(1, $descriptor->getPartitionsCount());
    }

    /**
     * Проверяет, что объект вернет список своих полей.
     */
    public function testGetFields(): void
    {
        $field1 = $this->getMockBuilder(EntityField::class)->getMock();
        $field1->method('getName')->willReturn('test1');

        $field2 = $this->getMockBuilder(EntityField::class)->getMock();
        $field2->method('getName')->willReturn('test2');

        $fields = [$field1, $field2];

        $descriptor = $this->createDescriptor([
            'fields' => $fields,
        ]);

        $this->assertSame($fields, $descriptor->getFields());
    }

    /**
     * Проверяет, что объект проверяет наличие поля с указанным именем.
     */
    public function testHasField(): void
    {
        $field = $this->getMockBuilder(EntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $descriptor = $this->createDescriptor([
            'fields' => ['test' => $field],
        ]);

        $this->assertTrue($descriptor->hasField('test1'));
        $this->assertFalse($descriptor->hasField('test'));
    }

    /**
     * Проверяет, что объект вернет поле по указанному имени.
     */
    public function testGetField(): void
    {
        $field = $this->getMockBuilder(EntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $descriptor = $this->createDescriptor([
            'fields' => ['test' => $field],
        ]);

        $this->assertSame($field, $descriptor->getField('test1'));
    }

    /**
     * Проверяет, что объект выбросит исключение, если не найдет поле по имени.
     */
    public function testGetFieldException(): void
    {
        $field = $this->getMockBuilder(EntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $descriptor = $this->createDescriptor([
            'fields' => ['test' => $field],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $descriptor->getField('test2');
    }

    /**
     * Проверяет, что объект выбросит исключение, если поля не были заданы.
     */
    public function testEmptyFieldsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createDescriptor(
            [
                'fields' => null,
            ]
        );
    }

    /**
     * Проверяет, что объект выбросит исключение, если указано неверное поле.
     */
    public function testWrongFieldTypeException(): void
    {
        $field1 = $this->getMockBuilder(EntityField::class)->getMock();
        $field2 = 123;
        $fields = [$field1, $field2];

        $this->expectException(\InvalidArgumentException::class);
        $this->createDescriptor(
            [
                'fields' => $fields,
            ]
        );
    }

    /**
     * Проверяет, что объект выбросит исключение, если имена полей дублируются.
     */
    public function testDoublingFieldsNamesException(): void
    {
        $field1 = $this->getMockBuilder(EntityField::class)->getMock();
        $field1->method('getName')->willReturn('test');

        $field2 = $this->getMockBuilder(EntityField::class)->getMock();
        $field2->method('getName')->willReturn('test');

        $this->expectException(\InvalidArgumentException::class);
        $this->createDescriptor(
            [
                'fields' => [
                    $field1,
                    $field2,
                ],
            ]
        );
    }

    /**
     * Проверяет, что объект правильно сопоставляет имена файлов для вставки с шаблоном.
     */
    public function testIsFileNameFitsXmlInsertFileMask(): void
    {
        $fileMask = '*_test_*.xml';

        $descriptor = $this->createDescriptor([
            'insertFileMask' => $fileMask,
        ]);

        $this->assertTrue($descriptor->isFileNameFitsXmlInsertFileMask('123_test_321.xml'));
        $this->assertFalse($descriptor->isFileNameFitsXmlInsertFileMask('123_321_test.xml'));
    }

    /**
     * Проверяет, что объект правильно сопоставляет имена файлов для вставки с регулярным выражением.
     */
    public function testIsFileNameFitsXmlInsertFileMaskRegexp(): void
    {
        $fileMask = '/^AS_NORMATIVE_DOCS_\d+_.*\.XML$/';

        $descriptor = $this->createDescriptor([
            'insertFileMask' => $fileMask,
        ]);

        $this->assertTrue(
            $descriptor->isFileNameFitsXmlInsertFileMask('AS_NORMATIVE_DOCS_20210909_04eb2443-d30d-4e39-8e69-78143490027f.XML')
        );
        $this->assertFalse(
            $descriptor->isFileNameFitsXmlInsertFileMask('AS_NORMATIVE_DOCS_PARAMS_20210909_04eb2443-d30d-4e39-8e69-78143490027f.XML')
        );
    }

    /**
     * Проверяет, что объект правильно сопоставляет имена файлов для удаления с шаблоном.
     */
    public function testIsFileNameFitsXmlDeleteFileMask(): void
    {
        $fileMask = '*_test_*.xml';

        $descriptor = $this->createDescriptor([
            'deleteFileMask' => $fileMask,
        ]);

        $this->assertTrue($descriptor->isFileNameFitsXmlDeleteFileMask('123_test_321.xml'));
        $this->assertFalse($descriptor->isFileNameFitsXmlDeleteFileMask('123_321_test.xml'));
    }

    /**
     * Проверяет, что объект правильно сопоставляет имена файлов для удаления с регулярным выражением.
     */
    public function testIsFileNameFitsXmlDeleteFileMaskRegexp(): void
    {
        $fileMask = '#^\d{3}_test_.*\.xml#';

        $descriptor = $this->createDescriptor([
            'deleteFileMask' => $fileMask,
        ]);

        $this->assertTrue($descriptor->isFileNameFitsXmlDeleteFileMask('123_test_321.xml'));
        $this->assertFalse($descriptor->isFileNameFitsXmlDeleteFileMask('test_test_321.xml'));
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
        $field->method('getName')->willReturn('test');

        $resultOptions = array_merge([
            'name' => 'Test',
            'xmlPath' => '/test/item',
            'fields' => [$field],
        ], $options);

        return new BaseEntityDescriptor($resultOptions);
    }
}
