<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\UnpackTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;

/**
 * Тест для задачи, которая распаковывает архив из параметра в состоянии.
 *
 * @internal
 */
final class UnpackTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно загружает ссылку.
     */
    public function testRun(): void
    {
        $sourcePath = '/test.file';
        $destinationPath = '/test_path';

        $unpack = $this->mock(Unpacker::class);
        $unpack->expects($this->once())
            ->method('unpack')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $source): bool => $source->getPathname() === $sourcePath
                ),
                $this->callback(
                    fn (\SplFileInfo $destination): bool => $destination->getPathname() === $destinationPath
                )
            );

        $state = $this->createDefaultStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $sourcePath,
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => $destinationPath,
            ]
        );

        $task = new UnpackTask($unpack);

        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь к архиву.
     *
     * @throws \Exception
     */
    public function testRunNoSourceException(): void
    {
        $destinationPath = '/test_path';
        $unpack = $this->mock(Unpacker::class);

        $state = $this->createDefaultStateMock(
            [
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => $destinationPath,
            ]
        );

        $task = new UnpackTask($unpack);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь куда распаковать файл.
     *
     * @throws \Exception
     */
    public function testRunNoDestinationException(): void
    {
        $sourcePath = '/test.file';
        $unpack = $this->mock(Unpacker::class);

        $state = $this->createDefaultStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $sourcePath,
            ]
        );

        $task = new UnpackTask($unpack);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
