<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasEntity;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntityField;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит описание сущности во внутреннем массиве.
 *
 * @internal
 */
class FiasEntityImplTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно вернет имя сущности.
     */
    public function testGetName(): void
    {
        $name = 'name';

        $descriptor = $this->createEntity([
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
        $this->expectExceptionMessage('Name param is required');
        $this->createEntity(
            [
                'name' => '  ',
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет xpath сущности в файле.
     */
    public function testGetXmlPath(): void
    {
        $xpath = '/root/Test';

        $descriptor = $this->createEntity([
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
        $this->expectExceptionMessage('XmlPath param is required');
        $this->createEntity(
            [
                'xmlPath' => '  ',
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет описание сущности.
     */
    public function testGetDescription(): void
    {
        $description = 'test description';

        $descriptor = $this->createEntity([
            'description' => $description,
        ]);

        $this->assertSame($description, $descriptor->getDescription());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустое описание.
     */
    public function testGetDescriptionDefault(): void
    {
        $descriptor = $this->createEntity();

        $this->assertSame('', $descriptor->getDescription());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для загрузки данных.
     */
    public function testGetXmlInsertFileMask(): void
    {
        $file = 'test_file_*.xml';

        $descriptor = $this->createEntity([
            'insertFileMask' => $file,
        ]);

        $this->assertSame($file, $descriptor->getXmlInsertFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для загрузки данных.
     */
    public function testGetXmlInsertFileMaskDefault(): void
    {
        $descriptor = $this->createEntity();

        $this->assertSame('', $descriptor->getXmlInsertFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для удаления данных.
     */
    public function testGetXmlDeleteFileMask(): void
    {
        $file = 'test_file_*.xml';

        $descriptor = $this->createEntity([
            'deleteFileMask' => $file,
        ]);

        $this->assertSame($file, $descriptor->getXmlDeleteFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для удаления данных.
     */
    public function testGetXmlDeleteFileMaskDefault(): void
    {
        $descriptor = $this->createEntity();

        $this->assertSame('', $descriptor->getXmlDeleteFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет количество частей, на которые
     * нужно разбить таблицу с данной сущностью.
     */
    public function testGetPartitionsCount(): void
    {
        $count = 5;

        $descriptor = $this->createEntity([
            'partitionsCount' => $count,
        ]);

        $this->assertSame($count, $descriptor->getPartitionsCount());
    }

    /**
     * Проверяет, что объект выбросит исключение, если число партиций меньше 1.
     */
    public function testGetPartitionsCountLessThan1Exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Partititons count can't be less than 1");
        $this->createEntity([
            'partitionsCount' => 0,
        ]);
    }

    /**
     * Проверяет, что объект вернет список своих полей.
     */
    public function testGetFields(): void
    {
        $field1 = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field1->method('getName')->willReturn('test1');

        $field2 = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field2->method('getName')->willReturn('test2');

        $fields = [$field1, $field2];

        $descriptor = $this->createEntity([
            'fields' => $fields,
        ]);

        $this->assertSame($fields, $descriptor->getFields());
    }

    /**
     * Проверяет, что объект проверяет наличие поля с указанным именем.
     */
    public function testHasField(): void
    {
        $field = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $descriptor = $this->createEntity([
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
        $field = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $descriptor = $this->createEntity([
            'fields' => ['test' => $field],
        ]);

        $this->assertSame($field, $descriptor->getField('test1'));
    }

    /**
     * Проверяет, что объект выбросит исключение, если не найдет поле по имени.
     */
    public function testGetFieldException(): void
    {
        $field = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $descriptor = $this->createEntity([
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
        $this->expectExceptionMessage("Fields array can't be empty");
        $this->createEntity(
            [
                'fields' => [],
            ]
        );
    }

    /**
     * Проверяет, что объект выбросит исключение, если имена полей дублируются.
     */
    public function testDoublingFieldsNamesException(): void
    {
        $field1 = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field1->method('getName')->willReturn('test_field_name');

        $field2 = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field2->method('getName')->willReturn('test_field_name');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('test_field_name');
        $this->createEntity(
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

        $descriptor = $this->createEntity([
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

        $descriptor = $this->createEntity([
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

        $descriptor = $this->createEntity([
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

        $descriptor = $this->createEntity([
            'deleteFileMask' => $fileMask,
        ]);

        $this->assertTrue($descriptor->isFileNameFitsXmlDeleteFileMask('123_test_321.xml'));
        $this->assertFalse($descriptor->isFileNameFitsXmlDeleteFileMask('test_test_321.xml'));
    }

    /**
     * Создает объект по умолчанию.
     *
     * @psalm-suppress MixedArgument
     */
    private function createEntity(array $options = []): FiasEntityImpl
    {
        $field = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field->method('getName')->willReturn('test');

        return new FiasEntityImpl(
            $options['name'] ?? 'name',
            $options['xmlPath'] ?? '/test/item',
            $options['fields'] ?? [$field],
            $options['description'] ?? '',
            $options['partitionsCount'] ?? 1,
            $options['insertFileMask'] ?? '',
            $options['deleteFileMask'] ?? '',
        );
    }
}
