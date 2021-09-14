<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\Task\SelectFilesToProceedTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;
use stdClass;

/**
 * Тест для задачи, которая выбирает файлы из папки для загрузки в базу на основе данных из EntityManager.
 *
 * @internal
 */
class SelectFilesToProceedTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если не найдет параметр с папкой, в которую распакованные файлы.
     *
     * @throws Exception
     */
    public function testRunEmptyUnpackToException(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $state = $this->createDefaultStateMock();

        $task = new SelectFilesToProceedTask($entityManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если апка, в которую должны быть распакованы файлы не существует.
     *
     * @throws Exception
     */
    public function testRunNonExitedUnpackToException(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();

        $state = $this->createDefaultStateMock(
            [
                Task::EXTRACT_TO_FOLDER_PARAM => new SplFileInfo(__DIR__ . '/test'),
            ]
        );

        $task = new SelectFilesToProceedTask($entityManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект правильно получит список файлов для обработки.
     *
     * @throws Exception
     */
    public function testRun(): void
    {
        $fixturesFolder = __DIR__ . '/_fixtures';

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')->willReturnMap(
            [
                ['SelectFilesToProceedTaskTest_insert.xml', $descriptor],
                ['SelectFilesToProceedTaskTest_nested_insert.xml', $descriptor],
            ]
        );

        $entityManager->method('getDescriptorByDeleteFile')->willReturnMap(
            [
                ['SelectFilesToProceedTaskTest_delete.xml', $descriptor],
                ['SelectFilesToProceedTaskTest_nested_delete.xml', $descriptor],
            ]
        );

        $entityManager->method('getClassByDescriptor')->willReturnMap(
            [
                [$descriptor, stdClass::class],
            ]
        );

        $state = new ArrayState();
        $state->setAndLockParameter(Task::EXTRACT_TO_FOLDER_PARAM, new SplFileInfo($fixturesFolder));

        $task = new SelectFilesToProceedTask($entityManager);
        $task->run($state);

        $this->assertSame(
            [
                $fixturesFolder . '/SelectFilesToProceedTaskTest_delete.xml',
                $fixturesFolder . '/SelectFilesToProceedTaskTest_insert.xml',
                $fixturesFolder . '/nested/SelectFilesToProceedTaskTest_nested_delete.xml',
                $fixturesFolder . '/nested/SelectFilesToProceedTaskTest_nested_insert.xml',
            ],
            $state->getParameter(Task::FILES_TO_PROCEED)
        );
    }

    /**
     * Проверяет, что объект правильно получит список файлов для обработки с использованием фильтра.
     *
     * @throws Exception
     */
    public function testRunWithFilter(): void
    {
        $fixturesFolder = __DIR__ . '/_fixtures';

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')->willReturnMap(
            [
                ['SelectFilesToProceedTaskTest_insert.xml', $descriptor],
                ['SelectFilesToProceedTaskTest_nested_insert.xml', $descriptor],
            ]
        );

        $entityManager->method('getClassByDescriptor')->willReturnMap(
            [
                [$descriptor, stdClass::class],
            ]
        );

        $filter = $this->getMockBuilder(Filter::class)->getMock();
        $filter->method('test')->willReturnCallback(
            function (SplFileInfo $file) use ($fixturesFolder) {
                return ((string) $file) === $fixturesFolder . '/nested/SelectFilesToProceedTaskTest_nested_insert.xml';
            }
        );

        $state = new ArrayState();
        $state->setAndLockParameter(Task::EXTRACT_TO_FOLDER_PARAM, new SplFileInfo($fixturesFolder));

        $task = new SelectFilesToProceedTask($entityManager, $filter);
        $task->run($state);

        $this->assertSame(
            [
                $fixturesFolder . '/nested/SelectFilesToProceedTaskTest_nested_insert.xml',
            ],
            $state->getParameter(Task::FILES_TO_PROCEED)
        );
    }
}
