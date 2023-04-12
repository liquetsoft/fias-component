<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasThread;

use Liquetsoft\Fias\Component\Exception\FiasThreadException;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadParams;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadRunnerSymfonyCreatorImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Тест для объекта, который создает symfony process.
 *
 * @internal
 */
class FiasThreadRunnerSymfonyCreatorImplTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если указан пустой путь до бинарника.
     */
    public function testConstructEmptyBinException(): void
    {
        $this->expectException(FiasThreadException::class);
        $this->expectExceptionMessage("Path to bin can't be empty");
        new FiasThreadRunnerSymfonyCreatorImpl('   ', 'test');
    }

    /**
     * Проверяет, что объект выбросит исключение, если указан пустой путь до бинарника.
     */
    public function testConstructEmptyCommandException(): void
    {
        $this->expectException(FiasThreadException::class);
        $this->expectExceptionMessage("Command name can't be empty");
        new FiasThreadRunnerSymfonyCreatorImpl('/artisan.php', '  ');
    }

    /**
     * Проверяет, что объект правильно создаст процесс.
     */
    public function testCreate(): void
    {
        $pathToBin = '/artisan.php';
        $command = 'test:test';
        $phpExcutablePath = '/test/php';
        $params = ['test_param_name' => 'test param value'];

        /** @var MockObject&PhpExecutableFinder */
        $phpExcutableFinder = $this->getMockBuilder(PhpExecutableFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phpExcutableFinder->method('find')->willReturn($phpExcutablePath);

        /** @var MockObject&FiasThreadParams */
        $thread = $this->getMockBuilder(FiasThreadParams::class)->getMock();
        $thread->method('all')->willReturn($params);

        /** @var MockObject&FiasThreadParams */
        $thread1 = $this->getMockBuilder(FiasThreadParams::class)->getMock();
        $thread1->method('all')->willReturn([]);

        $creator = new FiasThreadRunnerSymfonyCreatorImpl($pathToBin, $command, $phpExcutableFinder);
        $processes = $creator->create([$thread, $thread1]);

        $this->assertCount(2, $processes);
        $this->assertFalse($processes[0]->isOutputDisabled());
        $this->assertNull($processes[0]->getTimeout());
        $this->assertStringContainsString($pathToBin, $processes[0]->getCommandLine());
        $this->assertStringContainsString($command, $processes[0]->getCommandLine());
        $this->assertStringContainsString($phpExcutablePath, $processes[0]->getCommandLine());
        $this->assertSame(json_encode($params), $processes[0]->getInput());
    }

    /**
     * Проверяет, что объект выбросит исключение, если не найдет путь до php инарника.
     */
    public function testCreatePhpBinaryNotFountException(): void
    {
        $pathToBin = '/artisan.php';
        $command = 'test:test';
        $params = ['test_param_name' => 'test param value'];

        /** @var MockObject&PhpExecutableFinder */
        $phpExcutableFinder = $this->getMockBuilder(PhpExecutableFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phpExcutableFinder->method('find')->willReturn(false);

        /** @var MockObject&FiasThreadParams */
        $thread = $this->getMockBuilder(FiasThreadParams::class)->getMock();
        $thread->method('all')->willReturn($params);

        $creator = new FiasThreadRunnerSymfonyCreatorImpl($pathToBin, $command, $phpExcutableFinder);

        $this->expectException(FiasThreadException::class);
        $this->expectExceptionMessage("Can't find php binary");
        $creator->create([$thread]);
    }
}
