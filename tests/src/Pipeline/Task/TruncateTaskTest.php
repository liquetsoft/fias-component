<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\State;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\TruncateTask;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая очищает таблицы для всех сущностей из менеджера сущностей.
 */
class TruncateTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект читает и записывает данные.
     */
    public function testRun()
    {
        $classes = [
            'Test\Class1',
            'Test\Class2',
        ];

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getBindedClasses')->will($this->returnValue($classes));

        $truncated = [];
        $storage = $this->getMockBuilder(Storage::class)->getMock();
        $storage->expects($this->once())->method('start');
        $storage->expects($this->once())->method('stop');
        $storage->method('truncate')->will($this->returnCallback(function ($className) use (&$truncated) {
            $truncated[] = $className;
        }));

        $state = $this->getMockBuilder(State::class)->getMock();

        $task = new TruncateTask($entityManager, $storage);
        $task->run($state);

        $this->assertSame($classes, $truncated);
    }
}
