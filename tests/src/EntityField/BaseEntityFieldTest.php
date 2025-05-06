<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityField;

use Liquetsoft\Fias\Component\EntityField\BaseEntityField;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит описание поля сущности во внутреннем массиве.
 *
 * @internal
 */
final class BaseEntityFieldTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно вернет имя поля.
     */
    public function testGetName(): void
    {
        $name = $this->createFakeData()->word();

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
    public function testGetDescription(): void
    {
        $description = $this->createFakeData()->word();

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
    public function testEmptyNameException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createField(
            [
                'name' => null,
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет тип поля.
     */
    public function testGetType(): void
    {
        $type = $this->createFakeData()->word();

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
    public function testEmptyTypeException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createField(
            [
                'type' => null,
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет дополнительный тип поля.
     */
    public function testGetSubType(): void
    {
        $subType = $this->createFakeData()->word();

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
    public function testGetSubTypeDefault(): void
    {
        $field = $this->createField();

        $this->assertSame('', $field->getSubType());
    }

    /**
     * Проверяет, что объект правильно вернет длину поля.
     */
    public function testGetLength(): void
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
    public function testGetLengthDefault(): void
    {
        $field = $this->createField();

        $this->assertNull($field->getLength());
    }

    /**
     * Проверяет, что объект правильно вернет флаг для null.
     */
    public function testIsNullable(): void
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
    public function testIsNullableDefault(): void
    {
        $field = $this->createField();

        $this->assertFalse($field->isNullable());
    }

    /**
     * Проверяет, что объект правильно вернет флаг первичного ключа.
     */
    public function testIsPrimary(): void
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
    public function testIsPrimaryDefault(): void
    {
        $field = $this->createField();

        $this->assertFalse($field->isPrimary());
    }

    /**
     * Проверяет, что объект правильно вернет флаг ключа.
     */
    public function testIsIndex(): void
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
    public function testIsIndexDefault(): void
    {
        $field = $this->createField();

        $this->assertFalse($field->isIndex());
    }

    /**
     * Проверяет, что объект вернет исключение, если поле и primary и index.
     */
    public function testIsIndexDoublingException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
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
    public function testIsPartition(): void
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
    public function testIsPartitionDefault(): void
    {
        $field = $this->createField();

        $this->assertFalse($field->isPartition());
    }

    /**
     * Создает объект по умолчанию.
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
