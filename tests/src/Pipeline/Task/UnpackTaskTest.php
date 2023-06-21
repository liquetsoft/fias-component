<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\UnpackTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasFileSelectorCase;
use Liquetsoft\Fias\Component\Tests\FileSystemCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use Liquetsoft\Fias\Component\Tests\UnpackerCase;

/**
 * Тест для задачи, которая распаковывает файлы из архива.
 *
 * @internal
 */
class UnpackTaskTest extends BaseCase
{
    use PipelineCase;
    use LoggerCase;
    use FileSystemCase;
    use FiasFileSelectorCase;
    use UnpackerCase;

    /**
     * Проверяет, что объект распакует только ие файлы, которые нуждаются в распаковке.
     *
     * @psalm-suppress MixedMethodCall
     */
    public function testRun(): void
    {
        $archive = '/archive';
        $file = '/file';
        $target = '/target';
        $extracted = '/extracted';

        $file1 = $this->createFiasFileSelectorFileMock('/test_1');
        $file2 = $this->createFiasFileSelectorFileMock($file, 10, $archive);
        $file3 = $this->createFiasFileSelectorFileMock('/test_2');
        $filesToProceed = [$file1, $file2, $file3];

        $targetSplFileInfo = $this->createSplFileInfoMock($target);
        $archiveSplFileInfo = $this->createSplFileInfoMock($archive);
        $extractedSplFileInfo = $this->createSplFileInfoMock($extracted);
        $fs = $this->createFileSystemMock();
        $fs->method('makeFileInfo')->willReturnCallback(
            fn (mixed $file): \SplFileInfo => match ($file) {
                $target => $targetSplFileInfo,
                $archive => $archiveSplFileInfo,
                $extracted => $extractedSplFileInfo,
                default => throw new \Exception("File {$file} can't be converted")
            }
        );

        $unpacker = $this->createUnpackerMock();
        $unpacker->expects($this->once())
            ->method('extractEntity')
            ->with(
                $this->identicalTo($archiveSplFileInfo),
                $this->identicalTo($file),
                $this->identicalTo($targetSplFileInfo),
            )
            ->willReturn($extracted);

        $changedState = $this->createPipelineStateMock();
        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::EXTRACT_TO_FOLDER->value => $target,
                PipelineStateParam::FILES_TO_PROCEED->value => $filesToProceed,
            ]
        );
        $state->expects($this->once())
            ->method('with')
            ->with(
                $this->identicalTo(PipelineStateParam::FILES_TO_PROCEED),
                $this->callback(
                    fn (array $files): bool => $files[1]->getPath() === $extracted
                )
            )
            ->willReturn($changedState);

        $logger = $this->createLoggerMockExpectsMessages(
            [
                [
                    'message' => 'Unpacking file',
                    'context' => [
                        'archive' => $archive,
                        'file' => $file,
                        'target' => $target,
                    ],
                ],
            ]
        );

        $task = new UnpackTask($unpacker, $fs);
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
        $target = '/target';

        $targetSplFileInfo = $this->createSplFileInfoMock($target);
        $fs = $this->createFileSystemMock();
        $fs->method('makeFileInfo')->willReturnCallback(
            fn (mixed $file): \SplFileInfo => match ($file) {
                $target => $targetSplFileInfo,
                default => throw new \Exception("File {$file} can't be converted")
            }
        );

        $unpacker = $this->createUnpackerMock();

        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::EXTRACT_TO_FOLDER->value => $target,
                PipelineStateParam::FILES_TO_PROCEED->value => ['test'],
            ]
        );

        $task = new UnpackTask($unpacker, $fs);

        $this->expectExceptionObject(
            TaskException::create(
                'File must be instance of %s',
                FiasFileSelectorFile::class
            )
        );
        $task->run($state);
    }
}
