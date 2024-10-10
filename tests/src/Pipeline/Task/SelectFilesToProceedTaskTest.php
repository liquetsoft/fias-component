<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelector;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\SelectFilesToProceedTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\UnpackerFile;

/**
 * Тест для задания, которое проверяет все файлы в архиве ФИАС
 * и выбирает только те, которые можно обработать.
 *
 * @internal
 */
final class SelectFilesToProceedTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если файл архива не существует.
     */
    public function testRunUnexistedFileException(): void
    {
        $selector = $this->mock(FiasFileSelector::class);
        $state = $this->createStateMock();

        $task = new SelectFilesToProceedTask($selector);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект правильно получит список файлов для обработки.
     */
    public function testRun(): void
    {
        $pathToArchive = __DIR__ . '/_fixtures/SelectFilesToProceedTaskTest/testRun.zip';

        $expectedFiles = [
            $this->mock(UnpackerFile::class),
            $this->mock(UnpackerFile::class),
        ];

        $selector = $this->mock(FiasFileSelector::class);
        $selector->expects($this->once())
            ->method('selectFiles')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $t): bool => $t->getRealPath() === $pathToArchive
                )
            )
            ->willReturn($expectedFiles);

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $pathToArchive,
            ]
        );

        $task = new SelectFilesToProceedTask($selector);
        $newState = $task->run($state);
        $res = $newState->getParameter(StateParameter::FILES_TO_PROCEED);

        $this->assertSame($expectedFiles, $res);
    }

    /**
     * Проверяет, что объект завергит процесс, если не найдет файлов для обработки.
     */
    public function testRunNothingFound(): void
    {
        $pathToArchive = __DIR__ . '/_fixtures/SelectFilesToProceedTaskTest/testRun.zip';

        $selector = $this->mock(FiasFileSelector::class);
        $selector->expects($this->any())->method('selectFiles')->willReturn([]);

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_DOWNLOAD_FILE->value => $pathToArchive,
            ]
        );

        $task = new SelectFilesToProceedTask($selector);
        $newState = $task->run($state);

        $this->assertTrue($newState->isCompleted());
    }
}
