<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Pipeline\Task\UnpackTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для задачи, которая распаковывает архив из параметра в состоянии.
 *
 * @internal
 */
class UnpackTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно загружает ссылку.
     *
     * @throws \Exception
     */
    public function testRun(): void
    {
        $sourcePath = __DIR__ . '/test.file';
        $source = new \SplFileInfo($sourcePath);
        $destinationPath = __DIR__;
        $destination = new \SplFileInfo($destinationPath);

        /** @var MockObject&Unpacker */
        $unpack = $this->getMockBuilder(Unpacker::class)->getMock();
        $unpack->expects($this->once())
            ->method('unpack')
            ->with(
                $this->callback(
                    function (\SplFileInfo $source) use ($sourcePath) {
                        return $source->getPathname() === $sourcePath;
                    }
                ),
                $this->callback(
                    function (\SplFileInfo $destination) use ($destinationPath) {
                        return $destination->getPathname() === $destinationPath;
                    }
                )
            );

        $state = $this->createDefaultStateMock(
            [
                Task::DOWNLOAD_TO_FILE_PARAM => $source,
                Task::EXTRACT_TO_FOLDER_PARAM => $destination,
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
        $destination = new \SplFileInfo(__DIR__);

        /** @var MockObject&Unpacker */
        $unpack = $this->getMockBuilder(Unpacker::class)->getMock();

        $state = $this->createDefaultStateMock(
            [
                Task::EXTRACT_TO_FOLDER_PARAM => $destination,
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
        $source = new \SplFileInfo(__DIR__ . '/test.file');

        /** @var MockObject&Unpacker */
        $unpack = $this->getMockBuilder(Unpacker::class)->getMock();

        $state = $this->createDefaultStateMock(
            [
                Task::DOWNLOAD_TO_FILE_PARAM => $source,
            ]
        );

        $task = new UnpackTask($unpack);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
