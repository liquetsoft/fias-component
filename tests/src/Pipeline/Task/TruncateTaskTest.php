<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Pipeline\Task\TruncateTask;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для задачи, которая очищает таблицы для всех сущностей из менеджера сущностей.
 *
 * @internal
 */
class TruncateTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект читает и записывает данные.
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $classes = [
            'Test\Class1',
            'Test\Class2',
        ];

        /** @var MockObject&EntityManager */
        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getBindedClasses')->willReturn($classes);

        $truncated = [];
        /** @var MockObject&Storage */
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('supportsClass')
            ->willReturnCallback(
                function (string $className) use (&$insertedData) {
                    return $className === 'Test\Class2';
                }
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
