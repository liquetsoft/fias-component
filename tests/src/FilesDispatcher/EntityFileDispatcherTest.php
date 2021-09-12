<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FilesDispatcher;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\FilesDispatcher\EntityFileDispatcher;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который разбивает файлы на потоки по именам сущностей, к которым файлы относятся.
 *
 * @internal
 */
class EntityFileDispatcherTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно разбивает на потоки файлы.
     *
     * @dataProvider dispatchProvider
     */
    public function testDispatch(array $files, int $processCount, array $expected): void
    {
        $entityManager = $this->getEntityManagerMock();

        $dispatcher = new EntityFileDispatcher($entityManager);
        $dispatchedFiles = $dispatcher->dispatch($files, $processCount);

        $this->assertSame($expected, $dispatchedFiles);
    }

    public function dispatchProvider(): array
    {
        $pathToFixtures = __DIR__ . '/_fixtures';

        return [
            'regular case' => [
                [
                    $pathToFixtures . '/test_del.xml',
                    $pathToFixtures . '/test.xml',
                    $pathToFixtures . '/test_1.xml',
                    $pathToFixtures . '/test_2.xml',
                    $pathToFixtures . '/test_1_del.xml',
                    $pathToFixtures . '/test_2_del.xml',
                ],
                2,
                [
                    [
                        $pathToFixtures . '/test.xml',
                        $pathToFixtures . '/test_del.xml',
                    ],
                    [
                        $pathToFixtures . '/test_1.xml',
                        $pathToFixtures . '/test_1_del.xml',
                        $pathToFixtures . '/test_2.xml',
                        $pathToFixtures . '/test_2_del.xml',
                    ],
                ],
            ],
            'empty list' => [
                [],
                10,
                [],
            ],
            'single process' => [
                [
                    $pathToFixtures . '/test_del.xml',
                    $pathToFixtures . '/test.xml',
                    $pathToFixtures . '/test_1.xml',
                    $pathToFixtures . '/test_2.xml',
                    $pathToFixtures . '/test_1_del.xml',
                    $pathToFixtures . '/test_2_del.xml',
                ],
                1,
                [
                    [
                        $pathToFixtures . '/test.xml',
                        $pathToFixtures . '/test_del.xml',
                        $pathToFixtures . '/test_1.xml',
                        $pathToFixtures . '/test_1_del.xml',
                        $pathToFixtures . '/test_2.xml',
                        $pathToFixtures . '/test_2_del.xml',
                    ],
                ],
            ],
        ];
    }

    private function getEntityManagerMock(): EntityManager
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->willReturn('entity');

        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->willReturn('entity_1');

        $descriptor2 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor2->method('getName')->willReturn('entity_2');

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')->willReturnMap(
            [
                ['test.xml', $descriptor],
                ['test_1.xml', $descriptor1],
                ['test_2.xml', $descriptor2],
            ]
        );
        $entityManager->method('getDescriptorByDeleteFile')->willReturnMap(
            [
                ['test_del.xml', $descriptor],
                ['test_1_del.xml', $descriptor1],
                ['test_2_del.xml', $descriptor2],
            ]
        );

        return $entityManager;
    }
}
