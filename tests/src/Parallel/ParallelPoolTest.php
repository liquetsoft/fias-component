<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Parallel;

use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Parallel\ParallelPool;
use Liquetsoft\Fias\Component\Parallel\ParallelTask;
use Liquetsoft\Fias\Component\Exception\ParallelException;
use InvalidArgumentException;

/**
 * Тест для пула параллельного выполнения задач.
 */
class ParallelPoolTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение при попытке задать несуществующий путь к файлу автозпгрузки.
     */
    public function testConstructWrongAutoloadException()
    {
        $this->expectException(InvalidArgumentException::class);
        new ParallelPool('test', 2);
    }

    /**
     * Проверяет, что объект выбросит исключение при попытке задать неверное число параллельных тредов.
     */
    public function testConstructWrongMaxParallelThreadsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new ParallelPool(null, -1);
    }

    /**
     * Проверяет, что задачи запишут строки в файл в верном порядке.
     */
    public function testRun()
    {
        $testFile = $this->getPathToTestFile('parallel', '');

        $task1 = new ParallelTask(function ($testFile) {
            file_put_contents($testFile, 'task1', FILE_APPEND);
            usleep(250000);
        }, ['f' => $testFile], 0);

        $task2 = new ParallelTask(function ($testFile) {
            file_put_contents($testFile, 'task2', FILE_APPEND);
        }, ['f' => $testFile], 0);

        $task3 = new ParallelTask(function ($testFile) {
            file_put_contents($testFile, 'task3', FILE_APPEND);
        }, ['f' => $testFile], 1);

        $pool = new ParallelPool(null, 2);
        $pool->addTask($task1);
        $pool->addTask($task2);
        $pool->addTask($task3);
        $pool->run();

        $this->assertSame('task1task3task2', file_get_contents($testFile));
    }

    /**
     * Проверяет, что пул правильно обработает исключение.
     */
    public function testRunException()
    {
        $task = new ParallelTask(function () { throw new InvalidArgumentException('Test exception.'); });

        $pool = new ParallelPool(null, 2);
        $pool->addTask($task);

        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage('Test exception.');
        $pool->run();
    }
}
