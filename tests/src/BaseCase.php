<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Liquetsoft\Fias\Component\Helper\IdHelper;
use Marvin255\FileSystemHelper\Exception\FileSystemException;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelper;
use PHPUnit\Framework\TestCase;

/**
 * Базовый класс для всех тестов.
 */
abstract class BaseCase extends TestCase
{
    private ?FileSystemHelper $fs = null;

    private ?string $tempDir = null;

    /**
     * @var array<string, int>
     */
    private array $counters = [];

    /**
     * Возвращает объект для работы с файловой системой.
     */
    public function fs(): FileSystemHelper
    {
        if ($this->fs === null) {
            $this->fs = FileSystemFactory::create();
        }

        return $this->fs;
    }

    /**
     * Возвращает путь до базовой папки для тестов.
     *
     * @throws \RuntimeException
     * @throws FileSystemException
     */
    protected function getTempDir(): string
    {
        if ($this->tempDir === null) {
            $this->tempDir = $this->fs()->getTmpDir() . \DIRECTORY_SEPARATOR . 'fias_component';
            $this->fs()->mkdirIfNotExist($this->tempDir);
            $this->fs()->emptyDir($this->tempDir);
        }

        return $this->tempDir;
    }

    /**
     * Создает тестовую директорию во временной папке и возвращает путь до нее.
     *
     * @throws \RuntimeException
     * @throws FileSystemException
     */
    protected function getPathToTestDir(string $name = ''): string
    {
        if ($name === '') {
            $name = IdHelper::createUniqueId();
        }

        $pathToFolder = $this->getTempDir() . \DIRECTORY_SEPARATOR . $name;

        $this->fs()->mkdir($pathToFolder);

        return $pathToFolder;
    }

    /**
     * Создает тестовый файл во временной директории.
     */
    protected function getPathToTestFile(string $name = '', ?string $content = null): string
    {
        if ($name === '') {
            $name = IdHelper::createUniqueId() . '.txt';
        }

        $pathToFile = $this->getTempDir() . \DIRECTORY_SEPARATOR . $name;
        $content = $content === null ? IdHelper::createUniqueId() : $content;
        if (file_put_contents($pathToFile, $content) === false) {
            throw new \RuntimeException("Can't create file {$pathToFile}");
        }

        return $pathToFile;
    }

    /**
     * Удаляет тестовую директорию и все ее содержимое.
     */
    protected function tearDown(): void
    {
        $this->counters = [];
        if ($this->tempDir) {
            $this->fs()->remove($this->tempDir);
        }

        parent::tearDown();
    }

    /**
     * Создает счетчик для willReturnCallback из-за того, что withConsecutive отменен.
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/4026
     */
    protected function incrementAndGetCounter(string $counterName = 'counter'): int
    {
        $counterName = strtolower(trim($counterName));
        if (!isset($this->counters[$counterName])) {
            $this->counters[$counterName] = 0;
        }
        $this->counters[$counterName]++;

        return $this->counters[$counterName];
    }

    /**
     * Возвращает значение счетчика.
     */
    protected function getCounter(string $counterName = 'counter'): int
    {
        $counterName = strtolower(trim($counterName));

        return $this->counters[$counterName] ?? 1;
    }
}
