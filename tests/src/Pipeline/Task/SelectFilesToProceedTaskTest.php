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
        $path = '/test/source';

        $expectedFiles = [
            $this->mock(UnpackerFile::class),
            $this->mock(UnpackerFile::class),
        ];

        $selector = $this->mock(FiasFileSelector::class);
        $selector->expects($this->once())
            ->method('supportSource')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $t): bool => $t->getPathname() === $path
                )
            )
            ->willReturn(true);
        $selector->expects($this->once())
            ->method('selectFiles')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $t): bool => $t->getPathname() === $path
                )
            )
            ->willReturn($expectedFiles);

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_SOURCE->value => $path,
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
        $path = '/test/source';

        $selector = $this->mock(FiasFileSelector::class);
        $selector->expects($this->once())->method('supportSource')->willReturn(true);
        $selector->expects($this->any())->method('selectFiles')->willReturn([]);

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_SOURCE->value => $path,
            ]
        );

        $task = new SelectFilesToProceedTask($selector);
        $newState = $task->run($state);

        $this->assertTrue($newState->isCompleted());
    }

    /**
     * Проверяет, что объект завергит процесс, если источник данных не поддерживается.
     */
    public function testRunUnsupportedSource(): void
    {
        $path = '/test/source';

        $selector = $this->mock(FiasFileSelector::class);
        $selector->expects($this->once())->method('supportSource')->willReturn(false);

        $state = $this->createStateMock(
            [
                StateParameter::PATH_TO_SOURCE->value => $path,
            ]
        );

        $task = new SelectFilesToProceedTask($selector);
        $newState = $task->run($state);

        $this->assertTrue($newState->isCompleted());
    }
}
