<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\CleanUpFilesToProceedTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Marvin255\FileSystemHelper\FileSystemHelper;

/**
 * Тест для задачи, которая удаляет по одному только те файлы, которые были в обработке.
 *
 * @internal
 */
final class CleanUpFilesToProceedTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно удали файлы.
     */
    public function testRun(): void
    {
        $file = 'test.txt';
        $file1 = 'test1.txt';

        $fs = $this->mock(FileSystemHelper::class);
        $fs->expects($this->exactly(2))
            ->method('removeIfExists')
            ->with(
                $this->callback(
                    fn (string $s): bool => $s === $file || $s === $file1
                )
            )
            ->willReturnArgument(0);

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => [
                    $file,
                    $file1,
                ],
            ]
        );

        $task = new CleanUpFilesToProceedTask($fs);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указаны файлы для обработки.
     */
    public function testRunFilesParamIsNotArrayException(): void
    {
        $fs = $this->mock(FileSystemHelper::class);

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => '',
            ]
        );

        $task = new CleanUpFilesToProceedTask($fs);

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('param must be an array');
        $task->run($state);
    }
}
