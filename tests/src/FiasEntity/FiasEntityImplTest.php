<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;
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

        $entity = $this->createEntity(
            [
                'name' => $name,
            ]
        );

        $this->assertSame($name, $entity->getName());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если имя не задано.
     */
    public function testEmptyNameException(): void
    {
        $this->expectException(FiasEntityException::class);
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

        $entity = $this->createEntity(
            [
                'xmlPath' => $xpath,
            ]
        );

        $this->assertSame($xpath, $entity->getXmlPath());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если xpath не задан.
     */
    public function testEmptyXmlPathException(): void
    {
        $this->expectException(FiasEntityException::class);
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

        $entity = $this->createEntity(
            [
                'description' => $description,
            ]
        );

        $this->assertSame($description, $entity->getDescription());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустое описание.
     */
    public function testGetDescriptionDefault(): void
    {
        $entity = $this->createEntity();

        $this->assertSame('', $entity->getDescription());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для загрузки данных.
     */
    public function testGetXmlInsertFileMask(): void
    {
        $file = 'test_file_*.xml';

        $entity = $this->createEntity(
            [
                'insertFileMask' => $file,
            ]
        );

        $this->assertSame($file, $entity->getXmlInsertFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для загрузки данных.
     */
    public function testGetXmlInsertFileMaskDefault(): void
    {
        $entity = $this->createEntity();

        $this->assertSame('', $entity->getXmlInsertFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет маску файла для удаления данных.
     */
    public function testGetXmlDeleteFileMask(): void
    {
        $file = 'test_file_*.xml';

        $entity = $this->createEntity(
            [
                'deleteFileMask' => $file,
            ]
        );

        $this->assertSame($file, $entity->getXmlDeleteFileMask());
    }

    /**
     * Проверяет, что объект по умолчанию вернет пустую маску файла для удаления данных.
     */
    public function testGetXmlDeleteFileMaskDefault(): void
    {
        $entity = $this->createEntity();

        $this->assertSame('', $entity->getXmlDeleteFileMask());
    }

    /**
     * Проверяет, что объект правильно вернет количество частей, на которые
     * нужно разбить таблицу с данной сущностью.
     */
    public function testGetPartitionsCount(): void
    {
        $count = 5;

        $entity = $this->createEntity(
            [
                'partitionsCount' => $count,
            ]
        );

        $this->assertSame($count, $entity->getPartitionsCount());
    }

    /**
     * Проверяет, что объект выбросит исключение, если число партиций меньше 1.
     */
    public function testGetPartitionsCountLessThan1Exception(): void
    {
        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage("Partititons count can't be less than 1");
        $this->createEntity(
            [
                'partitionsCount' => 0,
            ]
        );
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

        $entity = $this->createEntity(
            [
                'fields' => $fields,
            ]
        );

        $this->assertSame($fields, $entity->getFields());
    }

    /**
     * Проверяет, что объект проверяет наличие поля с указанным именем.
     */
    public function testHasField(): void
    {
        $field = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $entity = $this->createEntity(
            [
                'fields' => [
                    'test' => $field,
                ],
            ]
        );

        $this->assertTrue($entity->hasField('test1'));
        $this->assertFalse($entity->hasField('test'));
    }

    /**
     * Проверяет, что объект вернет поле по указанному имени.
     */
    public function testGetField(): void
    {
        $field = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $entity = $this->createEntity(
            [
                'fields' => ['test' => $field],
            ]
        );

        $this->assertSame($field, $entity->getField('test1'));
    }

    /**
     * Проверяет, что объект выбросит исключение, если не найдет поле по имени.
     */
    public function testGetFieldException(): void
    {
        $field = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field->method('getName')->willReturn('test1');

        $entity = $this->createEntity(
            [
                'fields' => [
                    'test' => $field,
                ],
            ]
        );

        $this->expectException(FiasEntityException::class);
        $entity->getField('test2');
    }

    /**
     * Проверяет, что объект выбросит исключение, если поля не были заданы.
     */
    public function testEmptyFieldsException(): void
    {
        $this->expectException(FiasEntityException::class);
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

        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage('All fields names must be unique, got duplicate: test_field_name');
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
     * Проверяет, что объект правильно сопоставляет имена файлов для вставки.
     *
     * @dataProvider provideIsFileNameFitsXmlInsertFileMask
     */
    public function testIsFileNameFitsXmlInsertFileMask(string $fileMask, string $fileName, bool $awaits): void
    {
        $entity = $this->createEntity(
            [
                'insertFileMask' => $fileMask,
            ]
        );
        $res = $entity->isFileNameFitsXmlInsertFileMask($fileName);

        $this->assertSame($awaits, $res);
    }

    /**
     * Проверяет, что объект правильно сопоставляет имена файлов для удаления.
     *
     * @dataProvider provideIsFileNameFitsXmlInsertFileMask
     */
    public function testIsFileNameFitsXmlDeleteFileMask(string $fileMask, string $fileName, bool $awaits): void
    {
        $entity = $this->createEntity(
            [
                'deleteFileMask' => $fileMask,
            ]
        );
        $res = $entity->isFileNameFitsXmlDeleteFileMask($fileName);

        $this->assertSame($awaits, $res);
    }

    public function provideIsFileNameFitsXmlInsertFileMask(): array
    {
        return [
            'mask true' => [
                '*_test_*.xml',
                '123_test_321.xml',
                true,
            ],
            'mask false' => [
                '*_test_*.xml',
                '123_321_test.xml',
                false,
            ],
            'mask similar to regexp' => [
                'test#.*#',
                '123_321_test.xml',
                false,
            ],
            'mask case insensitive' => [
                'test*.xml',
                'TEST_123.XML',
                true,
            ],
            'mask string start' => [
                'test.xml',
                'testtest.xml',
                false,
            ],
            'mask string end' => [
                'test.xml',
                'test.xmlxml',
                false,
            ],
            'mask with regexp delimiter' => [
                'test/.*',
                'test/.xml',
                true,
            ],
            'regexp true' => [
                '/^AS_NORMATIVE_DOCS_\d+_.*\.XML$/',
                'AS_NORMATIVE_DOCS_20210909_04eb2443-d30d-4e39-8e69-78143490027f.XML',
                true,
            ],
            'regexp false' => [
                '/^AS_NORMATIVE_DOCS_\d+_.*\.XML$/',
                'AS_NORMATIVE_DOCS_PARAMS_20210909_04eb2443-d30d-4e39-8e69-78143490027f.XML',
                false,
            ],
            'empty string' => [
                '',
                'AS_NORMATIVE_DOCS_PARAMS_20210909_04eb2443-d30d-4e39-8e69-78143490027f.XML',
                false,
            ],
        ];
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
