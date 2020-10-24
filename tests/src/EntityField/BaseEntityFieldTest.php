<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityField;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityField\BaseEntityField;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит описание поля сущности во внутреннем массиве.
 */
class BaseEntityFieldTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно вернет имя поля.
     */
    public function testGetName()
    {
        $name = $this->createFakeData()->word;

        $field = $this->createField(
            [
                'name' => $name,
            ]
        );

        $this->assertSame($name, $field->getName());
    }

    /**
     * Проверяет, что объект правильно вернет описание поля.
     */
    public function testGetDescription()
    {
        $description = $this->createFakeData()->word;

        $field = $this->createField(
            [
                'description' => $description,
            ]
        );

        $this->assertSame($description, $field->getDescription());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если имя не задано.
     */
    public function testEmptyNameException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createField(
            [
                'name' => null,
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет тип поля.
     */
    public function testGetType()
    {
        $type = $this->createFakeData()->word;

        $field = $this->createField(
            [
                'type' => $type,
            ]
        );

        $this->assertSame($type, $field->getType());
    }

    /**
     * Проверяет, что объект правильно выбросит исключение, если тип не задан.
     */
    public function testEmptyTypeException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createField(
            [
                'type' => null,
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет дополнительный тип поля.
     */
    public function testGetSubType()
    {
        $subType = $this->createFakeData()->word;

        $field = $this->createField(
            [
                'subType' => $subType,
            ]
        );

        $this->assertSame($subType, $field->getSubType());
    }

    /**
     * Проверяет, что объект вернет пустую строку, если дополнительный тип не задан.
     */
    public function testGetSubTypeDefault()
    {
        $field = $this->createField();

        $this->assertSame('', $field->getSubType());
    }

    /**
     * Проверяет, что объект правильно вернет длину поля.
     */
    public function testGetLength()
    {
        $length = $this->createFakeData()->numberBetween(1, 255);

        $field = $this->createField(
            [
                'length' => $length,
            ]
        );

        $this->assertSame($length, $field->getLength());
    }

    /**
     * Проверяет, что объект вернет пустую строку, если дополнительный тип не задан.
     */
    public function testGetLengthDefault()
    {
        $field = $this->createField();

        $this->assertNull($field->getLength());
    }

    /**
     * Проверяет, что объект правильно вернет флаг для null.
     */
    public function testIsNullable()
    {
        $isNullable = true;

        $field = $this->createField(
            [
                'isNullable' => $isNullable,
            ]
        );

        $this->assertSame($isNullable, $field->isNullable());
    }

    /**
     * Проверяет, что объект вернет false, если флаг для null не задан.
     */
    public function testIsNullableDefault()
    {
        $field = $this->createField();

        $this->assertSame(false, $field->isNullable());
    }

    /**
     * Проверяет, что объект правильно вернет флаг первичного ключа.
     */
    public function testIsPrimary()
    {
        $isPrimary = true;

        $field = $this->createField(
            [
                'isPrimary' => $isPrimary,
            ]
        );

        $this->assertSame($isPrimary, $field->isPrimary());
    }

    /**
     * Проверяет, что объект вернет false, если флаг первичного ключа не задан.
     */
    public function testIsPrimaryDefault()
    {
        $field = $this->createField();

        $this->assertSame(false, $field->isPrimary());
    }

    /**
     * Проверяет, что объект правильно вернет флаг ключа.
     */
    public function testIsIndex()
    {
        $isIndex = true;

        $field = $this->createField(
            [
                'isIndex' => $isIndex,
            ]
        );

        $this->assertSame($isIndex, $field->isIndex());
    }

    /**
     * Проверяет, что объект вернет false, если флаг ключа не задан.
     */
    public function testIsIndexDefault()
    {
        $field = $this->createField();

        $this->assertSame(false, $field->isIndex());
    }

    /**
     * Проверяет, что объект вернет исключение, если поле и primary и index.
     */
    public function testIsIndexDoublingException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createField(
            [
                'isPrimary' => true,
                'isIndex' => true,
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет флаг секционирования.
     */
    public function testIsPartition()
    {
        $isPartition = true;

        $field = $this->createField(
            [
                'isPartition' => $isPartition,
            ]
        );

        $this->assertSame($isPartition, $field->isPartition());
    }

    /**
     * Проверяет, что объект вернет false, если флаг секционирования не задан.
     */
    public function testIsPartitionDefault()
    {
        $field = $this->createField();

        $this->assertSame(false, $field->isPartition());
    }

    /**
     * Создает объект по умолчанию.
     *
     * @param array $options
     *
     * @return BaseEntityField
     */
    protected function createField(array $options = []): BaseEntityField
    {
        $resultOptions = array_merge(
            [
                'name' => 'Test',
                'type' => 'string',
            ],
            $options
        );

        return new BaseEntityField($resultOptions);
    }
}
