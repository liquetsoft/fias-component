<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadPlannerImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasEntityCase;
use Liquetsoft\Fias\Component\Tests\FiasFileSelectorCase;

/**
 * Тест для объекта, который разбивает файлы по потокам.
 *
 * @internal
 */
class FiasThreadPlannerImplTest extends BaseCase
{
    use FiasEntityCase;
    use FiasFileSelectorCase;

    private const PATH_TO_INSERT = '/path/insert.xml';
    private const PATH_TO_DELETE = '/path/delete.xml';
    private const PATH_TO_INSERT_1 = '/path/insert_1.xml';
    private const PATH_TO_DELETE_1 = '/path/delete_1.xml';
    private const PATH_TO_INSERT_2 = '/path/insert_2.xml';
    private const PATH_TO_DELETE_2 = '/path/delete_2.xml';

    /**
     * Проверяет, что объект правильно разбивает на потоки файлы.
     *
     * @param array<string, int>                  $files
     * @param FiasFileSelectorFile[][]|\Exception $expected
     *
     * @dataProvider planProvider
     */
    public function testPlan(array $files, int $processCount, array|\Exception $expected): void
    {
        $fiasEntity = $this->createFiasEntityMock('entity_test');
        $fiasEntity->method('isFileNameFitsXmlInsertFileMask')->willReturnCallback(
            fn (string $toTest): bool => $toTest === self::PATH_TO_INSERT
        );
        $fiasEntity->method('isFileNameFitsXmlDeleteFileMask')->willReturnCallback(
            fn (string $toTest): bool => $toTest === self::PATH_TO_DELETE
        );

        $fiasEntity1 = $this->createFiasEntityMock('entity_test_1');
        $fiasEntity1->method('isFileNameFitsXmlInsertFileMask')->willReturnCallback(
            fn (string $toTest): bool => $toTest === self::PATH_TO_INSERT_1
        );
        $fiasEntity1->method('isFileNameFitsXmlDeleteFileMask')->willReturnCallback(
            fn (string $toTest): bool => $toTest === self::PATH_TO_DELETE_1
        );

        $fiasEntity2 = $this->createFiasEntityMock('entity_test_2');
        $fiasEntity2->method('isFileNameFitsXmlInsertFileMask')->willReturnCallback(
            fn (string $toTest): bool => $toTest === self::PATH_TO_INSERT_2
        );
        $fiasEntity2->method('isFileNameFitsXmlDeleteFileMask')->willReturnCallback(
            fn (string $toTest): bool => $toTest === self::PATH_TO_DELETE_2
        );

        $repo = $this->createFiasEntityRepoMock([$fiasEntity, $fiasEntity1, $fiasEntity2]);

        $fileMocks = [];
        foreach ($files as $path => $size) {
            $fileMocks[] = $this->createFiasFileSelectorFileMock($path, $size);
        }

        $planner = new FiasThreadPlannerImpl($repo);

        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        /** @var array */
        $result = array_map(
            fn (array $thread): array => array_map(
                fn (FiasFileSelectorFile $file): string => $file->getPath(),
                $thread
            ),
            $planner->plan($fileMocks, $processCount)
        );

        if (!($expected instanceof \Exception)) {
            $this->assertSame($expected, $result);
        }
    }

    public function planProvider(): array
    {
        return [
            'zero process count' => [
                [
                    self::PATH_TO_INSERT => 10,
                ],
                0,
                FiasThreadException::create('processesCount has to be more than 0'),
            ],
            'negative process count' => [
                [
                    self::PATH_TO_INSERT => 10,
                ],
                -1,
                FiasThreadException::create('processesCount has to be more than 0'),
            ],
            'empty list' => [
                [],
                100,
                [],
            ],
            'more allowed threads than files' => [
                [
                    self::PATH_TO_DELETE => 10,
                    self::PATH_TO_INSERT => 10,
                    self::PATH_TO_INSERT_1 => 100,
                    self::PATH_TO_DELETE_1 => 100,
                    self::PATH_TO_INSERT_2 => 1,
                    self::PATH_TO_DELETE_2 => 1,
                ],
                100,
                [
                    [self::PATH_TO_INSERT, self::PATH_TO_DELETE],
                    [self::PATH_TO_INSERT_1, self::PATH_TO_DELETE_1],
                    [self::PATH_TO_INSERT_2, self::PATH_TO_DELETE_2],
                ],
            ],
            'single thread' => [
                [
                    self::PATH_TO_INSERT => 10,
                    self::PATH_TO_DELETE => 10,
                    self::PATH_TO_INSERT_1 => 10,
                    self::PATH_TO_DELETE_1 => 10,
                ],
                1,
                [
                    [
                        self::PATH_TO_INSERT,
                        self::PATH_TO_DELETE,
                        self::PATH_TO_INSERT_1,
                        self::PATH_TO_DELETE_1,
                    ],
                ],
            ],
            'sorting by size' => [
                [
                    self::PATH_TO_INSERT => 51,
                    self::PATH_TO_DELETE => 50,
                    self::PATH_TO_INSERT_1 => 50,
                    self::PATH_TO_DELETE_1 => 50,
                    self::PATH_TO_INSERT_2 => 50,
                    self::PATH_TO_DELETE_2 => 50,
                ],
                2,
                [
                    [
                        self::PATH_TO_INSERT,
                        self::PATH_TO_DELETE,
                    ],
                    [
                        self::PATH_TO_INSERT_1,
                        self::PATH_TO_DELETE_1,
                        self::PATH_TO_INSERT_2,
                        self::PATH_TO_DELETE_2,
                    ],
                ],
            ],
            'files without entites' => [
                [
                    '/no/eneity/1' => 50,
                    '/no/eneity/2' => 50,
                    self::PATH_TO_INSERT => 100,
                    self::PATH_TO_DELETE => 100,
                    '/no/eneity/3' => 50,
                    '/no/eneity/4' => 50,
                ],
                100,
                [
                    [
                        self::PATH_TO_INSERT,
                        self::PATH_TO_DELETE,
                    ],
                ],
            ],
        ];
    }
}
