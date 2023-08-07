<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntityImpl;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntityType;

/**
 * Тест для объекта, который представляет файл в архиве.
 *
 * @internal
 */
class UnpackerEntityImplTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если имя будет не задано.
     */
    public function testConstructEmptyNameException(): void
    {
        $this->expectException(UnpackerException::class);
        $this->expectExceptionMessage("Entity name can't be empty");
        $this->createEntity(['name' => '  ']);
    }

    /**
     * Проверяет, что объект вернет правильный тип.
     */
    public function testGetType(): void
    {
        $value = UnpackerEntityType::DIRECTORY;

        $entity = $this->createEntity(['type' => $value]);
        $gotValue = $entity->getType();

        $this->assertSame($value, $gotValue);
    }

    /**
     * Проверяет, что объект вернет правильный индекс.
     */
    public function testGetIndex(): void
    {
        $value = 123;

        $entity = $this->createEntity(['index' => $value]);
        $gotValue = $entity->getIndex();

        $this->assertSame($value, $gotValue);
    }

    /**
     * Проверяет, что объект вернет правильный размер.
     */
    public function testGetSize(): void
    {
        $value = 123;

        $entity = $this->createEntity(['size' => $value]);
        $gotValue = $entity->getSize();

        $this->assertSame($value, $gotValue);
    }

    /**
     * Проверяет, что объект вернет правильное имя.
     */
    public function testGetName(): void
    {
        $value = 'test name';

        $entity = $this->createEntity(['name' => $value]);
        $gotValue = $entity->getName();

        $this->assertSame($value, $gotValue);
    }

    /**
     * Создает объект по умолчанию.
     *
     * @psalm-suppress MixedArgument
     */
    private function createEntity(array $options = []): UnpackerEntityImpl
    {
        return new UnpackerEntityImpl(
            $options['type'] ?? UnpackerEntityType::FILE,
            $options['name'] ?? 'name',
            $options['index'] ?? 0,
            $options['size'] ?? 0
        );
    }
}
