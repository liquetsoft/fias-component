<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadRunnerSymfony;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadRunnerSymfonyCreator;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\Process;

/**
 * Тест для объекта, который запускает трэды с помощью symfony process.
 *
 * @internal
 */
class FiasThreadRunnerSymfonyTest extends BaseCase
{
    use PipelineCase;

    /**
     * Проверяет, что объект правильно запустит трэды.
     */
    public function testRun(): void
    {
        $param = $this->createPipelineStateMock();
        $param1 = $this->createPipelineStateMock();

        /** @var Process&MockObject */
        $process = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $process->expects($this->once())->method('start');
        $process->expects($this->exactly(3))->method('isRunning')->willReturnCallback(
            fn (): bool => $this->incrementAndGetCounter('process_isRunning') <= 1
        );
        $process->expects($this->once())->method('isSuccessful')->willReturn(true);

        /** @var Process&MockObject */
        $process1 = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $process1->expects($this->once())->method('start');
        $process1->expects($this->exactly(2))->method('isRunning')->willReturnCallback(
            fn (): bool => $this->incrementAndGetCounter('process_1_isRunning') <= 1
        );
        $process1->expects($this->once())->method('isSuccessful')->willReturn(true);

        /** @var FiasThreadRunnerSymfonyCreator&MockObject */
        $creator = $this->getMockBuilder(FiasThreadRunnerSymfonyCreator::class)->getMock();
        $creator->method('create')
            ->willReturnCallback(
                fn (PipelineState $state): Process => match ($state) {
                    $param => $process,
                    $param1 => $process1,
                    default => throw new \Exception("Can't create process")
                }
            );

        $runner = new FiasThreadRunnerSymfony($creator, 0);
        $runner->run([$param, $param1]);
    }

    /**
     * Проверяет, что объект использует sleep между проверками статусов потоокв,
     * чтобы не забирать лишние ресурсы системы на ненужные проверки.
     */
    public function testRunWithSleep(): void
    {
        $param = $this->createPipelineStateMock();

        $process = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $process->expects($this->once())->method('start');
        $process->expects($this->once())->method('isRunning')->willReturn(false);
        $process->expects($this->once())->method('isSuccessful')->willReturn(true);

        /** @var FiasThreadRunnerSymfonyCreator&MockObject */
        $creator = $this->getMockBuilder(FiasThreadRunnerSymfonyCreator::class)->getMock();
        $creator->expects($this->once())->method('create')->willReturn($process);

        $runner = new FiasThreadRunnerSymfony($creator, 1);

        $start = microtime(true);
        $runner->run([$param]);
        $stop = microtime(true);

        $this->assertGreaterThanOrEqual(1, $stop - $start);
    }

    /**
     * Проверяет, что объект правильно обработает ошибку от трэда.
     */
    public function testRunThreadError(): void
    {
        $param = $this->createPipelineStateMock();

        $errorMessage = 'test error message';
        $process = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $process->expects($this->once())->method('start');
        $process->expects($this->once())->method('isRunning')->willReturn(false);
        $process->expects($this->once())->method('isSuccessful')->willReturn(false);
        $process->expects($this->once())->method('getErrorOutput')->willReturn($errorMessage);

        /** @var FiasThreadRunnerSymfonyCreator&MockObject */
        $creator = $this->getMockBuilder(FiasThreadRunnerSymfonyCreator::class)->getMock();
        $creator->expects($this->once())->method('create')->willReturn($process);

        $runner = new FiasThreadRunnerSymfony($creator, 0);

        $this->expectException(FiasThreadException::class);
        $this->expectExceptionMessage($errorMessage);
        $runner->run([$param]);
    }
}
