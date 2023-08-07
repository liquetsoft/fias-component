<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadPlanner;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadRunner;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\Task\SplitFilesToThreadsTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\FiasFileSelectorCase;
use Liquetsoft\Fias\Component\Tests\LoggerCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

/**
 * Тест для задачи, которая разбивает собранные файлы на части и запускает обработку в отдельных потоках.
 *
 * @internal
 */
class SplitFilesToThreadsTaskTest extends BaseCase
{
    use PipelineCase;
    use FiasFileSelectorCase;
    use LoggerCase;

    /**
     * Проверяет, что объект соберет файлы из архива.
     */
    public function testRun(): void
    {
        $maxThreadsCount = 2;

        $file = $this->createFiasFileSelectorFileMock();
        $file1 = $this->createFiasFileSelectorFileMock();
        $files = [$file, $file1];
        $splitFiles = [[$file], [$file1]];

        $stateThread = $this->createPipelineStateMock();
        $stateThread1 = $this->createPipelineStateMock();
        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::FILES_TO_PROCEED->value => $files,
            ]
        );
        $state->method('with')->willReturnCallback(
            function (PipelineStateParam $p, array $pFiles) use ($splitFiles, $stateThread, $stateThread1): PipelineState {
                if ($p === PipelineStateParam::FILES_TO_PROCEED && $pFiles === $splitFiles[0]) {
                    return $stateThread;
                } elseif ($p === PipelineStateParam::FILES_TO_PROCEED && $pFiles === $splitFiles[1]) {
                    return $stateThread1;
                }
                throw new \Exception('Param not found');
            }
        );

        $planner = $this->createPlannerMock();
        $planner->expects($this->once())
            ->method('plan')
            ->with(
                $this->identicalTo($files),
                $this->identicalTo($maxThreadsCount)
            )
            ->willReturn($splitFiles);

        $runner = $this->createRunnerMock();
        $runner->expects($this->once())
            ->method('run')
            ->with(
                $this->identicalTo([$stateThread, $stateThread1])
            );

        $logger = $this->createLoggerMockExpectsMessage(LogLevel::INFO, 'Files split to threads');

        $task = new SplitFilesToThreadsTask($planner, $runner, $maxThreadsCount);
        $task->injectLogger($logger);
        $stateToTest = $task->run($state);

        $this->assertSame($state, $stateToTest);
    }

    /**
     * Проверяет, что объект выбросит исключение, если файлы переданы в неверном формате.
     */
    public function testRunWrongFileTypeException(): void
    {
        $state = $this->createPipelineStateMock(
            [
                PipelineStateParam::FILES_TO_PROCEED->value => ['test'],
            ]
        );

        $planner = $this->createPlannerMock();
        $runner = $this->createRunnerMock();

        $task = new SplitFilesToThreadsTask($planner, $runner);

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage(FiasFileSelectorFile::class);

        $task->run($state);
    }

    /**
     * Создает мок для планировщика потоков.
     *
     * @return FiasThreadPlanner&MockObject
     */
    private function createPlannerMock(): FiasThreadPlanner
    {
        /** @var FiasThreadPlanner&MockObject */
        $planner = $this->getMockBuilder(FiasThreadPlanner::class)->getMock();

        return $planner;
    }

    /**
     * Создает мок для исполнителя потоков.
     *
     * @return FiasThreadRunner&MockObject
     */
    private function createRunnerMock(): FiasThreadRunner
    {
        /** @var FiasThreadRunner&MockObject */
        $runner = $this->getMockBuilder(FiasThreadRunner::class)->getMock();

        return $runner;
    }
}
