<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\SelectFilesToProceedTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая выбирает файлы из папки для загрузки в базу на основе данных из EntityManager.
 *
 * @internal
 */
final class SelectFilesToProceedTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если не найдет параметр с папкой, в которую распакованные файлы.
     */
    public function testRunEmptyUnpackToException(): void
    {
        $entityManager = $this->mock(EntityManager::class);
        $state = $this->createStateMock();

        $task = new SelectFilesToProceedTask($entityManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если апка, в которую должны быть распакованы файлы не существует.
     */
    public function testRunNonExitedUnpackToException(): void
    {
        $entityManager = $this->mock(EntityManager::class);

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_EXTRACT_FOLDER->value => __DIR__ . '/test',
            ]
        );

        $task = new SelectFilesToProceedTask($entityManager);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект правильно получит список файлов для обработки.
     */
    public function testRun(): void
    {
        $fixturesFolder = __DIR__ . '/_fixtures';

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();

        $entityManager = $this->mock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getDescriptorByInsertFile')
            ->willReturnMap(
                [
                    ['SelectFilesToProceedTaskTest_insert.xml', $descriptor],
                    ['SelectFilesToProceedTaskTest_nested_insert.xml', $descriptor],
                ]
            );
        $entityManager->expects($this->any())
            ->method('getDescriptorByDeleteFile')
            ->willReturnMap(
                [
                    ['SelectFilesToProceedTaskTest_delete.xml', $descriptor],
                    ['SelectFilesToProceedTaskTest_nested_delete.xml', $descriptor],
                ]
            );

        $entityManager->expects($this->any())
            ->method('getClassByDescriptor')
            ->willReturnMap(
                [
                    [$descriptor, \stdClass::class],
                ]
            );

        $state = new ArrayState();
        $state->setAndLockParameter(StateParameter::PATH_TO_EXTRACT_FOLDER, $fixturesFolder);

        $task = new SelectFilesToProceedTask($entityManager);
        $task->run($state);

        $this->assertSame(
            [
                $fixturesFolder . '/SelectFilesToProceedTaskTest_delete.xml',
                $fixturesFolder . '/SelectFilesToProceedTaskTest_insert.xml',
                $fixturesFolder . '/nested/SelectFilesToProceedTaskTest_nested_delete.xml',
                $fixturesFolder . '/nested/SelectFilesToProceedTaskTest_nested_insert.xml',
            ],
            $state->getParameter(StateParameter::FILES_TO_PROCEED)
        );
    }

    /**
     * Проверяет, что объект правильно получит список файлов для обработки с использованием фильтра.
     */
    public function testRunWithFilter(): void
    {
        $fixturesFolder = __DIR__ . '/_fixtures';

        $descriptor = $this->mock(EntityDescriptor::class);

        $entityManager = $this->mock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getDescriptorByInsertFile')->willReturnMap(
                [
                    ['SelectFilesToProceedTaskTest_insert.xml', $descriptor],
                    ['SelectFilesToProceedTaskTest_nested_insert.xml', $descriptor],
                ]
            );
        $entityManager->expects($this->any())
            ->method('getClassByDescriptor')->willReturnMap(
                [
                    [$descriptor, \stdClass::class],
                ]
            );

        $filter = $this->mock(Filter::class);
        $filter->expects($this->any())
            ->method('test')
            ->willReturnCallback(
                function (\SplFileInfo $file) use ($fixturesFolder) {
                    return ((string) $file) === $fixturesFolder . '/nested/SelectFilesToProceedTaskTest_nested_insert.xml';
                }
            );

        $state = new ArrayState();
        $state->setAndLockParameter(StateParameter::PATH_TO_EXTRACT_FOLDER, $fixturesFolder);

        $task = new SelectFilesToProceedTask($entityManager, $filter);
        $task->run($state);

        $this->assertSame(
            [
                $fixturesFolder . '/nested/SelectFilesToProceedTaskTest_nested_insert.xml',
            ],
            $state->getParameter(StateParameter::FILES_TO_PROCEED)
        );
    }
}
