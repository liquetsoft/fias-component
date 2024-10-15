<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\UnpackTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;

/**
 * Тест для задачи, которая распаковывает архив из параметра в состоянии.
 *
 * @internal
 */
final class UnpackTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно распаковывает файлы.
     */
    public function testRun(): void
    {
        $fileName = 'test.file';
        $destinationPath = '/test_path';
        $pathToUnpackedFile = '/test_path/test.file';
        $pathToRandomFile = '/test/random_file.txt';

        $archive = $this->mock(\SplFileInfo::class);
        $archive->expects($this->any())->method('getPathname')->willReturn('archive.zip');
        $archive->expects($this->any())->method('getRealPath')->willReturn('archive.zip');

        $unpackerResult = $this->mock(\SplFileInfo::class);
        $unpackerResult->expects($this->any())->method('getPathname')->willReturn($pathToUnpackedFile);
        $unpackerResult->expects($this->any())->method('getRealPath')->willReturn($pathToUnpackedFile);

        $unpackerFile = $this->mock(UnpackerFile::class);
        $unpackerFile->expects($this->any())->method('getArchiveFile')->willReturn($archive);
        $unpackerFile->expects($this->any())->method('getName')->willReturn($fileName);

        $unpack = $this->mock(Unpacker::class);
        $unpack->expects($this->once())
            ->method('unpackFile')
            ->with(
                $this->identicalTo($archive),
                $this->identicalTo($fileName),
                $this->callback(
                    fn (\SplFileInfo $destination): bool => $destination->getPathname() === $destinationPath
                )
            )
            ->willReturn($unpackerResult);

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => [
                    $unpackerFile,
                    $pathToRandomFile,
                ],
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => $destinationPath,
            ]
        );

        $task = new UnpackTask($unpack);
        $res = $task->run($state)->getParameter(StateParameter::FILES_TO_PROCEED);

        $this->assertSame(
            [
                $pathToUnpackedFile,
                $pathToRandomFile,
            ],
            $res
        );
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь куда распаковать файл.
     */
    public function testRunNoDestinationException(): void
    {
        $files = [
            $this->mock(UnpackerFile::class),
        ];

        $unpacker = $this->mock(Unpacker::class);

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => $files,
            ]
        );

        $task = new UnpackTask($unpacker);

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('Destination path must be a non empty string');
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указаны файлы для распаковки.
     */
    public function testRunFilesParamIsNotArrayException(): void
    {
        $destinationPath = '/test_path';

        $unpacker = $this->mock(Unpacker::class);

        $state = $this->createStateMock(
            [
                StateParameter::FILES_TO_PROCEED->value => '',
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => $destinationPath,
            ]
        );

        $task = new UnpackTask($unpacker);

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('param must be an array');
        $task->run($state);
    }
}
