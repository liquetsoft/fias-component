<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityField;

use Liquetsoft\Fias\Component\Entity\BaseEntityField;
use Liquetsoft\Fias\Component\Entity\EntityFieldSubTypes;
use Liquetsoft\Fias\Component\Entity\EntityFieldTypes;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит описание поля сущности во внутреннем массиве.
 *
 * @internal
 */
class BaseEntityFieldTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если указаны несовместимые тип и дополнительный тип.
     */
    public function testConstructWrongSubTypeException(): void
    {
        $type = EntityFieldTypes::INT;
        $subType = EntityFieldSubTypes::DATE;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subtype is not allowed for set type');
        $this->createField(
            [
                'type' => $type,
                'subType' => $subType,
            ]
        );
    }

    /**
     * Проверяет, что объект выбросит исключение, если указано пустое имя.
     */
    public function testConstructEmptyNameException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name is required');
        $this->createField(
            [
                'name' => '   ',
            ]
        );
    }

    /**
     * Проверяет, что объект выбросит исключение, если указаны индекс и первичный ключ одновременно.
     */
    public function testConstructIndexAndPrimaryTypeException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Primary field already has index');
        $this->createField(
            [
                'isPrimary' => true,
                'isIndex' => true,
            ]
        );
    }

    /**
     * Проверяет, что объект правильно вернет тип поля.
     */
    public function testGetType(): void
    {
        $type = EntityFieldTypes::INT;

        $field = $this->createField(
            [
                'type' => $type,
            ]
        );

        $this->assertSame($type, $field->getType());
    }

    /**
     * Проверяет, что объект правильно вернет дополнительный тип поля.
     */
    public function testGetSubType(): void
    {
        $type = EntityFieldTypes::STRING;
        $subType = EntityFieldSubTypes::DATE;

        $field = $this->createField(
            [
                'type' => $type,
                'subType' => $subType,
            ]
        );

        $this->assertSame($subType, $field->getSubType());
    }

    /**
     * Проверяет, что объект правильно вернет имя поля.
     */
    public function testGetName(): void
    {
        $name = 'name';

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
        $description = 'description';

        $field = $this->createField(
            [
                'description' => $description,
            ]
        );

        $this->assertSame($description, $field->getDescription());
    }

    /**
     * Проверяет, что объект правильно вернет длину поля.
     */
    public function testGetLength(): void
    {
        $length = 10;

        $field = $this->createField(
            [
                'length' => $length,
            ]
        );

        $this->assertSame($length, $field->getLength());
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
     * Создает объект по умолчанию.
     *
     * @psalm-suppress MixedArgument
     */
    private function createField(array $options = []): BaseEntityField
    {
        return new BaseEntityField(
            $options['type'] ?? EntityFieldTypes::STRING,
            $options['subType'] ?? EntityFieldSubTypes::NONE,
            $options['name'] ?? 'name',
            $options['description'] ?? 'description',
            $options['length'] ?? null,
            $options['isNullable'] ?? false,
            $options['isPrimary'] ?? false,
            $options['isIndex'] ?? false,
            $options['isPartition'] ?? false
        );
    }
}
