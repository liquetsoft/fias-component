<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasEntity;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityBinderImpl;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityRepository;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который связывает сущности с реализациями.
 *
 * @internal
 */
class FiasEntityBinderImplTest extends BaseCase
{
    /**
     * Проверяет, что объект найдет класс, привязанный к сущности.
     *
     * @psalm-param array<string, class-string> $bindings
     *
     * @dataProvider provideGetImplementationByEntityName
     */
    public function testGetImplementationByEntityName(array $bindings, string|FiasEntity $toSearh, ?string $awaits): void
    {
        /** @var FiasEntityRepository&MockObject */
        $repo = $this->getMockBuilder(FiasEntityRepository::class)->getMock();

        $binder = new FiasEntityBinderImpl($repo, $bindings);
        $impl = $binder->getImplementationByEntityName($toSearh);

        $this->assertSame($awaits, $impl);
    }

    public function provideGetImplementationByEntityName(): array
    {
        /** @var FiasEntity&MockObject */
        $enity = $this->getMockBuilder(FiasEntity::class)->getMock();
        $enity->method('getName')->willReturn('test');

        return [
            'has binding by string' => [
                ['test' => self::class],
                'test',
                self::class,
            ],
            'has binding by entity' => [
                ['test' => self::class],
                $enity,
                self::class,
            ],
            "doesn't have binding by string" => [
                [],
                'test',
                null,
            ],
            "doesn't have binding by entity" => [
                [],
                $enity,
                null,
            ],
            'binding name with whitespaces' => [
                [' test ' => self::class],
                'test',
                self::class,
            ],
            'binding name with uppercase' => [
                ['TEST' => self::class],
                'test',
                self::class,
            ],
            'entity name with whitespaces' => [
                ['test' => self::class],
                ' test ',
                self::class,
            ],
            'entity name with uppercase' => [
                ['test' => self::class],
                'TEST',
                self::class,
            ],
        ];
    }

    /**
     * Проверяет, что вернет сущность ФИАС, которая отностится к указанному классу.
     *
     * @psalm-param array<string, class-string> $bindings
     * @psalm-param class-string|object $toSearh
     *
     * @dataProvider provideGetEntityByImplementation
     */
    public function testGetEntityByImplementation(string $existedItemName, array $bindings, string|object $toSearh, ?string $awaits): void
    {
        /** @var FiasEntity&MockObject */
        $item = $this->getMockBuilder(FiasEntity::class)->getMock();
        $item->method('getName')->willReturn($existedItemName);

        /** @var FiasEntityRepository&MockObject */
        $repo = $this->getMockBuilder(FiasEntityRepository::class)->getMock();
        $repo->method('hasEntity')->willReturnCallback(fn (string $name): bool => $name === $existedItemName);
        $repo->method('getEntity')->willReturnCallback(
            fn (string $name): FiasEntity => match ($name) {
                $existedItemName => $item,
                default => throw new \Exception("Item with name '{$name}' is not found")
            }
        );

        $binder = new FiasEntityBinderImpl($repo, $bindings);
        $entity = $binder->getEntityByImplementation($toSearh);

        if ($entity === null) {
            $this->assertNull($entity);
        } else {
            $this->assertSame($awaits, $entity->getName());
        }
    }

    public function provideGetEntityByImplementation(): array
    {
        return [
            'item exists by name' => [
                'testItemName',
                ['testItemName' => self::class],
                self::class,
                'testItemName',
            ],
            'item exists by object' => [
                'testItemName',
                ['testItemName' => self::class],
                $this,
                'testItemName',
            ],
            "item doesn't exists by name" => [
                'testItemName',
                ['non_exist_key' => self::class],
                self::class,
                null,
            ],
            "item doesn't exists by object" => [
                'testItemName',
                ['non_exist_key' => self::class],
                $this,
                null,
            ],
            "binding doesn't exists" => [
                'testItemName',
                ['testItemName' => self::class],
                \Exception::class,
                null,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет всех сущностей, имеющих привязки.
     */
    public function testGetBoundEntities(): void
    {
        $entityName = 'entity';
        /** @var FiasEntity&MockObject */
        $entity = $this->getMockBuilder(FiasEntity::class)->getMock();

        $entity1Name = 'entity1';
        /** @var FiasEntity&MockObject */
        $entity1 = $this->getMockBuilder(FiasEntity::class)->getMock();

        $entity2Name = 'entity2';
        /** @var FiasEntity&MockObject */
        $entity2 = $this->getMockBuilder(FiasEntity::class)->getMock();

        /** @var FiasEntityRepository&MockObject */
        $repo = $this->getMockBuilder(FiasEntityRepository::class)->getMock();
        $repo->method('hasEntity')->willReturnCallback(
            fn (string $name): bool => match ($name) {
                $entityName, $entity1Name, $entity2Name => true,
                default => false
            }
        );
        $repo->method('getEntity')->willReturnCallback(
            fn (string $name): FiasEntity => match ($name) {
                $entityName => $entity,
                $entity1Name => $entity1,
                $entity2Name => $entity2,
                default => throw new \Exception("entity with name '{$name}' isn't found")
            }
        );

        $binder = new FiasEntityBinderImpl(
            $repo,
            [
                $entity1Name => self::class,
                $entity2Name => self::class,
            ]
        );
        $boundEntites = $binder->getBoundEntities();

        $this->assertSame([$entity1, $entity2], $boundEntites);
    }

    /**
     * Проверяет, что объект вернет правильный список связок.
     */
    public function testGetBindings(): void
    {
        $bindings = [
            'test' => self::class,
            'Test1' => self::class,
            ' test2 ' => self::class,
        ];
        $normalizedBindings = [
            'test' => self::class,
            'test1' => self::class,
            'test2' => self::class,
        ];

        /** @var FiasEntityRepository&MockObject */
        $repo = $this->getMockBuilder(FiasEntityRepository::class)->getMock();

        $binder = new FiasEntityBinderImpl($repo, $bindings);
        $resultBindings = $binder->getBindings();

        $this->assertSame($normalizedBindings, $resultBindings);
    }
}
