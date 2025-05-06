<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FilesDispatcher;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\FilesDispatcher\FilesDispatcherImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который разбивает файлы на потоки по именам сущностей, к которым файлы относятся.
 *
 * @internal
 */
final class FilesDispatcherImplTest extends BaseCase
{
    private const ENTITY_TO_FILES_MAP = [
        'entity' => [
            'insert' => 'entity.xml',
            'delete' => 'entity_del.xml',
        ],
        'entity_1' => [
            'insert' => 'entity_1.xml',
            'delete' => 'entity_1_del.xml',
        ],
    ];

    /**
     * Проверяет, что объект правильно разбивает на потоки файлы.
     *
     * @psalm-suppress MixedArrayAccess
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dispatchProvider')]
    public function testDispatch(array $files, int $processCount, array $expected): void
    {
        $entityManager = $this->getEntityManagerMock();

        $unpackerFiles = [];
        foreach ($files as $file) {
            $unpackerFile = $this->mock(UnpackerFile::class);
            $unpackerFile->expects($this->any())->method('getName')->willReturn($file['name'] ?? '');
            $unpackerFile->expects($this->any())->method('getSize')->willReturn($file['size'] ?? 0);
            $unpackerFiles[] = $unpackerFile;
        }

        $dispatcher = new FilesDispatcherImpl($entityManager);
        $threads = $dispatcher->dispatch($unpackerFiles, $processCount);

        $res = [];
        foreach ($threads as $threadId => $thread) {
            $res[$threadId] = $res[$threadId] ?? [];
            foreach ($thread as $file) {
                $res[$threadId][] = $file->getName();
            }
        }

        $this->assertSame($expected, $res);
    }

    public static function dispatchProvider(): array
    {
        return [
            'regular case' => [
                [
                    [
                        'name' => '/30/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        'size' => 180,
                    ],
                    [
                        'name' => '/31/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        'size' => 200,
                    ],
                    [
                        'name' => '/32/' . self::ENTITY_TO_FILES_MAP['entity_1']['insert'],
                        'size' => 10,
                    ],
                    [
                        'name' => '/33/' . self::ENTITY_TO_FILES_MAP['entity_1']['insert'],
                        'size' => 10,
                    ],
                    [
                        'name' => self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        'size' => 10,
                    ],
                ],
                2,
                [
                    [
                        '/31/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        self::ENTITY_TO_FILES_MAP['entity']['insert'],
                    ],
                    [
                        '/30/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        '/32/' . self::ENTITY_TO_FILES_MAP['entity_1']['insert'],
                        '/33/' . self::ENTITY_TO_FILES_MAP['entity_1']['insert'],
                    ],
                ],
            ],
            'single thread' => [
                [
                    [
                        'name' => '/30/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        'size' => 180,
                    ],
                    [
                        'name' => '/31/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        'size' => 200,
                    ],
                    [
                        'name' => '/32/' . self::ENTITY_TO_FILES_MAP['entity_1']['insert'],
                        'size' => 10,
                    ],
                ],
                1,
                [
                    [
                        '/31/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        '/30/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        '/32/' . self::ENTITY_TO_FILES_MAP['entity_1']['insert'],
                    ],
                ],
            ],
            'related entites' => [
                [
                    [
                        'name' => '/30/' . self::ENTITY_TO_FILES_MAP['entity']['delete'],
                        'size' => 180,
                    ],
                    [
                        'name' => '/31/' . self::ENTITY_TO_FILES_MAP['entity']['delete'],
                        'size' => 180,
                    ],
                    [
                        'name' => '/30/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        'size' => 250,
                    ],
                    [
                        'name' => '/31/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        'size' => 180,
                    ],
                ],
                2,
                [
                    [
                        '/30/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        '/30/' . self::ENTITY_TO_FILES_MAP['entity']['delete'],
                    ],
                    [
                        '/31/' . self::ENTITY_TO_FILES_MAP['entity']['insert'],
                        '/31/' . self::ENTITY_TO_FILES_MAP['entity']['delete'],
                    ],
                ],
            ],
            'empty list' => [
                [],
                10,
                [],
            ],
        ];
    }

    /**
     * @return EntityManager&MockObject
     */
    private function getEntityManagerMock(): EntityManager
    {
        $descriptors = [];
        foreach (array_keys(self::ENTITY_TO_FILES_MAP) as $entityName) {
            $descriptors[$entityName] = $this->mock(EntityDescriptor::class);
            $descriptors[$entityName]->expects($this->any())
                ->method('getName')
                ->willReturn($entityName);
        }

        $entityManager = $this->mock(EntityManager::class);

        $entityManager->expects($this->any())
            ->method('getDescriptorByInsertFile')
            ->willReturnCallback(
                function (string $f) use ($descriptors): ?EntityDescriptor {
                    foreach (self::ENTITY_TO_FILES_MAP as $entityName => $files) {
                        if ($f === $files['insert']) {
                            return $descriptors[$entityName] ?? null;
                        }
                    }

                    return null;
                }
            );

        $entityManager->expects($this->any())
            ->method('getDescriptorByDeleteFile')
            ->willReturnCallback(
                function (string $f) use ($descriptors): ?EntityDescriptor {
                    foreach (self::ENTITY_TO_FILES_MAP as $entityName => $files) {
                        if ($f === $files['delete']) {
                            return $descriptors[$entityName] ?? null;
                        }
                    }

                    return null;
                }
            );

        return $entityManager;
    }
}
