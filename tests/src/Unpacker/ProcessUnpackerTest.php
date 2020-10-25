<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Process\TemplateProcess;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Unpacker\ProcessUnpacker;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * Тест для объекта, который распаковывает архив с помощью командной строки.
 */
class ProcessUnpackerTest extends BaseCase
{
    /**
     * Проверяет, что объект создаст процесс и запустит его на исполнение.
     *
     * @throws UnpackerException
     */
    public function testUnpack()
    {
        $testArchive = __DIR__ . '/_fixtures/testUnpack.rar';
        $testDestination = $this->getPathToTestDir('testUnpack');

        $process = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();
        $process->expects($this->once())->method('mustRun');

        $commandTemplate = $this->getMockBuilder(TemplateProcess::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandTemplate->expects($this->once())
            ->method('createProcess')
            ->with($this->equalTo([
                'source' => $testArchive,
                'destination' => $testDestination,
            ]))->will(
                $this->returnValue($process)
            );

        $processUnpack = new ProcessUnpacker($commandTemplate);
        $processUnpack->unpack(
            new SplFileInfo($testArchive),
            new SplFileInfo($testDestination)
        );
    }

    /**
     * Проверяет, что объект перехватит исключение при распаковке.
     */
    public function testUnpackException()
    {
        $commandTemplate = $this->getMockBuilder(TemplateProcess::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandTemplate->method('createProcess')->will($this->throwException(new RuntimeException()));

        $this->expectException(UnpackerException::class);

        $processUnpack = new ProcessUnpacker($commandTemplate);
        $processUnpack->unpack(
            new SplFileInfo(__DIR__ . '/_fixtures/testUnpack.rar'),
            new SplFileInfo($this->getPathToTestDir('testUnpack'))
        );
    }
}
