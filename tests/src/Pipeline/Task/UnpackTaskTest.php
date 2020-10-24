<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Pipeline\Task\UnpackTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use RuntimeException;
use SplFileInfo;

/**
 * Тест для задачи, которая распаковывает архив из параметра в состоянии.
 */
class UnpackTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно загружает ссылку.
     *
     * @throws Exception
     */
    public function testRun()
    {
        $sourcePath = __DIR__ . '/test.file';
        $source = new SplFileInfo($sourcePath);
        $destinationPath = __DIR__;
        $destination = new SplFileInfo($destinationPath);

        $unpack = $this->getMockBuilder(Unpacker::class)->getMock();
        $unpack->expects($this->once())
            ->method('unpack')
            ->with(
                $this->callback(
                    function ($source) use ($sourcePath) {
                        return $source->getPathname() === $sourcePath;
                    }
                ),
                $this->callback(
                    function ($destination) use ($destinationPath) {
                        return $destination->getPathname() === $destinationPath;
                    }
                )
            );

        $state = $this->createTestState(
            function ($name) use ($source, $destination) {
                $return = null;
                if ($name === Task::DOWNLOAD_TO_FILE_PARAM) {
                    $return = $source;
                } elseif ($name === Task::EXTRACT_TO_FOLDER_PARAM) {
                    $return = $destination;
                }

                return $return;
            }
        );

        $task = new UnpackTask($unpack);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь к архиву.
     *
     * @throws Exception
     */
    public function testRunNoSourceException()
    {
        $destination = new SplFileInfo(__DIR__);

        $unpack = $this->getMockBuilder(Unpacker::class)->getMock();

        $state = $this->createTestState(
            function ($name) use ($destination) {
                return $name === Task::EXTRACT_TO_FOLDER_PARAM ? $destination : null;
            }
        );

        $task = new UnpackTask($unpack);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь куда распаковать файл.
     *
     * @throws Exception
     */
    public function testRunNoDestinationException()
    {
        $source = new SplFileInfo(__DIR__ . '/test.file');

        $unpack = $this->getMockBuilder(Unpacker::class)->getMock();

        $state = $this->createTestState(
            function ($name) use ($source) {
                return $name === Task::DOWNLOAD_TO_FILE_PARAM ? $source : null;
            }
        );

        $task = new UnpackTask($unpack);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Создает мок объекта состояния для тестов.
     *
     * @param callable|null $parameter
     *
     * @return State
     */
    private function createTestState(?callable $parameter): State
    {
        $state = $this->getMockBuilder(State::class)->getMock();
        if ($parameter) {
            $state->method('getParameter')
                ->will(
                    $this->returnCallback($parameter)
                );
        }

        if (!($state instanceof State)) {
            throw new RuntimeException('Wrong test state mock.');
        }

        return $state;
    }
}
