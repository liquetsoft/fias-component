<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Pipeline\Task\TruncateTask;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая очищает таблицы для всех сущностей из менеджера сущностей.
 *
 * @internal
 */
final class TruncateTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект читает и записывает данные.
     */
    public function testRun(): void
    {
        $classes = [
            'Test\Class1',
            'Test\Class2',
        ];

        $entityManager = $this->mock(EntityManager::class);
        $entityManager->method('getBindedClasses')->willReturn($classes);

        $truncated = [];
        $storage = $this->mock(Storage::class);
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supportsClass')
            ->willReturnCallback(
                fn (string $className): bool => $className === 'Test\Class2'
            );
        $storage->method('truncate')
            ->willReturnCallback(
                function (string $className) use (&$truncated): void {
                    $truncated[] = $className;
                }
            );

        $state = $this->createDefaultStateMock();

        $task = new TruncateTask($entityManager, $storage);
        $task->run($state);

        $this->assertSame(
            [
                'Test\Class2',
            ],
            $truncated
        );
    }
}
