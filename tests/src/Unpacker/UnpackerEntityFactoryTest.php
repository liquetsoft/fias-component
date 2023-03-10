<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntityFactory;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntityImpl;
use Liquetsoft\Fias\Component\Unpacker\UnpackerEntityType;

/**
 * Тест для фабрики, которая создает сущности архива.
 *
 * @internal
 */
class UnpackerEntityFactoryTest extends BaseCase
{
    /**
     * Проверяет, что фабрика создаст правильный объект.
     *
     * @dataProvider provideCreateFromZipStats
     */
    public function testCreateFromZipStats(mixed $stats, UnpackerEntityImpl|\Exception $awaits): void
    {
        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }

        $entity = UnpackerEntityFactory::createFromZipStats($stats);

        if ($awaits instanceof UnpackerEntityImpl) {
            $this->assertSame($awaits->getType(), $entity->getType());
            $this->assertSame($awaits->getIndex(), $entity->getIndex());
            $this->assertSame($awaits->getName(), $entity->getName());
            $this->assertSame($awaits->getSize(), $entity->getSize());
        }
    }

    public function provideCreateFromZipStats(): array
    {
        return [
            'correct stats for file' => [
                [
                    'crc' => 100,
                    'index' => 1,
                    'size' => 2,
                    'name' => 'test',
                ],
                new UnpackerEntityImpl(
                    UnpackerEntityType::FILE,
                    'test',
                    1,
                    2
                ),
            ],
            'correct stats for directory' => [
                [
                    'crc' => 0,
                    'index' => 1,
                    'size' => 2,
                    'name' => 'test',
                ],
                new UnpackerEntityImpl(
                    UnpackerEntityType::DIRECTORY,
                    'test',
                    1,
                    2
                ),
            ],
            'non string name' => [
                [
                    'crc' => 100,
                    'index' => 1,
                    'size' => 2,
                    'name' => 123,
                ],
                new UnpackerEntityImpl(
                    UnpackerEntityType::FILE,
                    '123',
                    1,
                    2
                ),
            ],
            'non int index' => [
                [
                    'crc' => 100,
                    'index' => '1',
                    'size' => 2,
                    'name' => 'name',
                ],
                new UnpackerEntityImpl(
                    UnpackerEntityType::FILE,
                    'name',
                    1,
                    2
                ),
            ],
            'non int size' => [
                [
                    'crc' => 100,
                    'index' => 1,
                    'size' => '2',
                    'name' => 'name',
                ],
                new UnpackerEntityImpl(
                    UnpackerEntityType::FILE,
                    'name',
                    1,
                    2
                ),
            ],
            'non array stats' => [
                'stats',
                new UnpackerException('Stats must be an array instance'),
            ],
        ];
    }
}
