<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadRunnerSymfonyCreatorImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Tests\PipelineCase;
use Liquetsoft\Fias\Component\Tests\SerializerCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Тест для объекта, который создает symfony process.
 *
 * @internal
 */
class FiasThreadRunnerSymfonyCreatorImplTest extends BaseCase
{
    use SerializerCase;
    use PipelineCase;

    /**
     * Проверяет, что объект выбросит исключение, если указан пустой путь до бинарника.
     */
    public function testConstructEmptyBinException(): void
    {
        $this->expectException(FiasThreadException::class);
        $this->expectExceptionMessage("Path to bin can't be empty");
        new FiasThreadRunnerSymfonyCreatorImpl(
            '   ',
            'test',
            $this->createSerializerMock()
        );
    }

    /**
     * Проверяет, что объект выбросит исключение, если указан пустой путь до бинарника.
     */
    public function testConstructEmptyCommandException(): void
    {
        $this->expectException(FiasThreadException::class);
        $this->expectExceptionMessage("Command name can't be empty");
        new FiasThreadRunnerSymfonyCreatorImpl(
            '/artisan.php',
            '  ',
            $this->createSerializerMock()
        );
    }

    /**
     * Проверяет, что объект правильно создаст процесс.
     */
    public function testCreate(): void
    {
        $pathToBin = '/artisan.php';
        $command = 'test:test';
        $phpExcutablePath = '/test/php';
        $jsonInput = '{test:"test"}';

        /** @var MockObject&PhpExecutableFinder */
        $phpExcutableFinder = $this->getMockBuilder(PhpExecutableFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phpExcutableFinder->method('find')->willReturn($phpExcutablePath);

        $thread = $this->createPipelineStateMock();

        $serializer = $this->createSerializerMockAwaitSerialization(
            $thread,
            JsonEncoder::FORMAT,
            $jsonInput
        );

        $creator = new FiasThreadRunnerSymfonyCreatorImpl(
            $pathToBin,
            $command,
            $serializer,
            $phpExcutableFinder
        );
        $process = $creator->create($thread);

        $this->assertFalse($process->isOutputDisabled());
        $this->assertNull($process->getTimeout());
        $this->assertStringContainsString($pathToBin, $process->getCommandLine());
        $this->assertStringContainsString($command, $process->getCommandLine());
        $this->assertStringContainsString($phpExcutablePath, $process->getCommandLine());
        $this->assertSame($jsonInput, $process->getInput());
    }

    /**
     * Проверяет, что объект выбросит исключение, если не найдет путь до php бинарника.
     */
    public function testCreatePhpBinaryNotFoundException(): void
    {
        $pathToBin = '/artisan.php';
        $command = 'test:test';
        $jsonInput = '{test:"test"}';

        /** @var MockObject&PhpExecutableFinder */
        $phpExcutableFinder = $this->getMockBuilder(PhpExecutableFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phpExcutableFinder->method('find')->willReturn(false);

        $thread = $this->createPipelineStateMock();

        $serializer = $this->createSerializerMockAwaitSerialization(
            $thread,
            JsonEncoder::FORMAT,
            $jsonInput
        );

        $creator = new FiasThreadRunnerSymfonyCreatorImpl(
            $pathToBin,
            $command,
            $serializer,
            $phpExcutableFinder
        );

        $this->expectException(FiasThreadException::class);
        $this->expectExceptionMessage("Can't find php binary");
        $creator->create($thread);
    }
}
