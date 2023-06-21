<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\CleanFilesToProceedTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasFileSelectorCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;

/**
 * Тест для задачи, которая распаковывает файлы из архива.
 *
 * @internal
 */
class CleanFilesToProceedTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FileSystemCase;
    use FiasFileSelectorCase;

    /**
     * Проверяет, что объект удалит все нераспакованные файлы.
     *
     * @psalm-suppress MixedMethodCall
     */
    public function testRun(): void
    {
        $file1Path = '/test_1';
        $file1 = $this->createFiasFileSelectorFileMock($file1Path);
        $file2 = $this->createFiasFileSelectorFileMock('/test_2', 10, '/archive');
        $filesToProceed = [$file1, $file2];

        $fs = $this->createFileSystemMock();
        $fs->expects($this->once())
            ->method('removeIfExists')
            ->with($this->identicalTo($file1Path));

        $changedState = $this->createPipelineStateMock();
        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::FILES_TO_PROCEED->value => $filesToProceed,
            ]
        );
        $state->expects($this->once())
            ->method('without')
            ->with(
                $this->identicalTo(PipelineStateParam::FILES_TO_PROCEED)
            )
            ->willReturn($changedState);

        $logger = $this->createLoggerMockExpectsMessages(
            [
                [
                    'message' => 'Remove file',
                    'context' => [
                        'file' => $file1Path,
                    ],
                ],
            ]
        );

        $task = new CleanFilesToProceedTask($fs);
        $task->injectLogger($logger);
        $newState = $task->run($state);

        $this->assertSame($changedState, $newState);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в параметре передан неверный тип файла.
     *
     * @psalm-suppress MixedMethodCall
     */
    public function testRunWrongFileTypeException(): void
    {
        $fs = $this->createFileSystemMock();

        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::FILES_TO_PROCEED->value => ['test'],
            ]
        );

        $task = new CleanFilesToProceedTask($fs);

        $this->expectExceptionObject(
            TaskException::create(
                'File must be instance of %s',
                FiasFileSelectorFile::class
            )
        );
        $task->run($state);
    }
}
