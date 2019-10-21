<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\State;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\Task\SelectFilesToProceedTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для задачи, которая выбирает файлы из папки для загрузки в базу на основе данных из EntityManager.
 */
class SelectFilesToProceedTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если не найдет параметр с папкой, в которую распаковыны файлы.
     */
    public function testRunEmptyUnpackToException()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $state = new ArrayState;

        $task = new SelectFilesToProceedTask($entityManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если апка, в которую должны быть распакованы файлы не существует.
     */
    public function testRunNonExitedUnpackToException()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();

        $state = new ArrayState;
        $state->setParameter(
            Task::EXTRACT_TO_FOLDER_PARAM,
            new SplFileInfo(__DIR__ . '/test')
        );

        $task = new SelectFilesToProceedTask($entityManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект правильно получит список файлов для обработки.
     */
    public function testRun()
    {
        $fixturesFolder = __DIR__ . '/_fixtures';

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')->will($this->returnCallback(function ($file) use ($descriptor) {
            return $file === 'SelectFilesToProceedTaskTest_insert.xml' ? $descriptor : null;
        }));
        $entityManager->method('getDescriptorByDeleteFile')->will($this->returnCallback(function ($file) use ($descriptor) {
            return $file === 'SelectFilesToProceedTaskTest_delete.xml' ? $descriptor : null;
        }));
        $entityManager->method('getClassByDescriptor')->will($this->returnCallback(function ($testDescriptor) use ($descriptor) {
            return $testDescriptor === $descriptor ? SelectFilesToProceedTaskObject::class : null;
        }));

        $state = new ArrayState;
        $state->setParameter(Task::EXTRACT_TO_FOLDER_PARAM, new SplFileInfo($fixturesFolder));

        $task = new SelectFilesToProceedTask($entityManager);
        $task->run($state);

        $this->assertSame(
            [$fixturesFolder . '/SelectFilesToProceedTaskTest_insert.xml'],
            $state->getParameter(Task::FILES_TO_INSERT_PARAM)
        );
        $this->assertSame(
            [$fixturesFolder . '/SelectFilesToProceedTaskTest_delete.xml'],
            $state->getParameter(Task::FILES_TO_DELETE_PARAM)
        );
    }
}

/**
 * Мок для проверки задачи, которая выбирает файлы для обработки.
 */
class SelectFilesToProceedTaskObject
{
}
