<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\Pipe\Pipe;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\ApplyNestedPipelineToFileTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая применяет вложенную цепочку задач для каждого файла из состояния.
 *
 * @internal
 */
final class ApplyNestedPipelineToFileTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно примеяет пайплайн.
     */
    public function testRun(): void
    {
        $file = 'test.txt';
        $file1 = 'test1.txt';

        $pipe = $this->mock(Pipe::class);
        $pipe->expects($this->exactly(2))
            ->method('run')
            ->with(
                $this->callback(
                    fn (State $s): bool => $s->getParameter(StateParameter::FILES_TO_PROCEED) === [$file]
                        || $s->getParameter(StateParameter::FILES_TO_PROCEED) === [$file1]
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

        $task = new ApplyNestedPipelineToFileTask($pipe);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указаны файлы для обработки.
     */
    public function testRunFilesParamIsNotArrayException(): void
    {
        $pipe = $this->mock(Pipe::class);

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => '',
            ]
        );

        $task = new ApplyNestedPipelineToFileTask($pipe);

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('param must be an array');
        $task->run($state);
    }
}
