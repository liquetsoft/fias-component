<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityRepositoryImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит список всех сущностей во внутреннем массиве.
 *
 * @internal
 */
class FiasEntityRepositoryImplTest extends BaseCase
{
    /**
     * Проверяет, что объект вернет список всех сущностей.
     */
    public function testGetAllEntities(): void
    {
        $entity1 = $this->getMockBuilder(FiasEntity::class)->getMock();
        $entity2 = $this->getMockBuilder(FiasEntity::class)->getMock();

        $repository = new FiasEntityRepositoryImpl([$entity1, $entity2]);
        $all = $repository->getAllEntities();

        $this->assertSame([$entity1, $entity2], $all);
    }

    /**
     * Проверяет, что объект вернет правду, если содержит указанную сущность.
     *
     * @dataProvider provideHasEntity
     */
    public function testHasEntity(string $entityName, string $searchName, bool $awaits): void
    {
        $entity = $this->getMockBuilder(FiasEntity::class)->getMock();
        $entity->method('getName')->willReturn($entityName);

        $repository = new FiasEntityRepositoryImpl([$entity]);
        $hasEntity = $repository->hasEntity($searchName);

        $this->assertSame($awaits, $hasEntity);
    }

    public function provideHasEntity(): array
    {
        return [
            'has entity' => [
                'test',
                'test',
                true,
            ],
            "doesn't have entity" => [
                'test_1',
                'test_2',
                false,
            ],
            'wrong case' => [
                'TeSt',
                'tEsT',
                true,
            ],
            'whitespaces' => [
                '  test  ',
                ' test ',
                true,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет сущность по ее имени.
     *
     * @dataProvider provideGetEntity
     */
    public function testGetEntity(string $entityName, string $searchName, ?\Exception $exception = null): void
    {
        $entity = $this->getMockBuilder(FiasEntity::class)->getMock();
        $entity->method('getName')->willReturn($entityName);

        $repository = new FiasEntityRepositoryImpl([$entity]);

        if ($exception) {
            $this->expectExceptionObject($exception);
        }

        $gotEntity = $repository->getEntity($searchName);

        if ($exception === null) {
            $this->assertSame($entity, $gotEntity);
        }
    }

    public function provideGetEntity(): array
    {
        return [
            'has entity' => [
                'test',
                'test',
            ],
            "doesn't have entity" => [
                'test_1',
                'test_2',
                new FiasEntityException("Can't find entity with name 'test_2'"),
            ],
            'wrong case' => [
                'TeSt',
                'tEsT',
            ],
            'whitespaces' => [
                '  test  ',
                ' test ',
            ],
        ];
    }
}
