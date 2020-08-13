<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Pipeline\Task\UnpackTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use SplFileInfo;

/**
 * Тест для задачи, которая распаковывает архив из параметра в состоянии.
 */
class UnpackTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно загружает ссылку.
     */
    public function testRun()
    {
        $sourcePath = __DIR__ . '/test.file';
        $source = new SplFileInfo($sourcePath);
        $destinationPath = __DIR__;
        $destination = new SplFileInfo($destinationPath);

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();

        $unpacker = $this->getMockBuilder(Unpacker::class)->getMock();
        $unpacker->expects($this->once())->method('unpack')->with(
            $this->callback(function ($source) use ($sourcePath) {
                return $source->getPathname() === $sourcePath;
            }),
            $this->callback(function ($destination) use ($destinationPath) {
                return $destination->getPathname() === $destinationPath;
            })
        );

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($source, $destination) {
            $return = null;
            if ($name === Task::DOWNLOAD_TO_FILE_PARAM) {
                $return = $source;
            } elseif ($name === Task::EXTRACT_TO_FOLDER_PARAM) {
                $return = $destination;
            }

            return $return;
        }));

        $task = new UnpackTask($unpacker, $entityManager);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь к архиву.
     */
    public function testRunNoSourceException()
    {
        $destination = new SplFileInfo(__DIR__);

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();

        $unpacker = $this->getMockBuilder(Unpacker::class)->getMock();
        $unpacker->expects($this->never())->method('unpack');

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($destination) {
            return $name === Task::EXTRACT_TO_FOLDER_PARAM ? $destination : null;
        }));

        $task = new UnpackTask($unpacker, $entityManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь куда распаковать файл.
     */
    public function testRunNoDestinationException()
    {
        $source = new SplFileInfo(__DIR__ . '/test.file');

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();

        $unpacker = $this->getMockBuilder(Unpacker::class)->getMock();
        $unpacker->expects($this->never())->method('unpack');

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($source) {
            return $name === Task::DOWNLOAD_TO_FILE_PARAM ? $source : null;
        }));

        $task = new UnpackTask($unpacker, $entityManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
