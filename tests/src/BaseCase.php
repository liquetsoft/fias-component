<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Faker\Factory;
use Faker\Generator;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * Базовый класс для всех тестов.
 */
abstract class BaseCase extends TestCase
{
    /**
     * @var Generator|null
     */
    private $faker;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * Возвращает объект php faker для генерации случайных данных.
     *
     * Использует ленивую инициализацию и создает объект faker только при первом
     * запросе, для всех последующих запросов возвращает тот же самый объект,
     * который был создан в первый раз.
     *
     * @return Generator
     */
    public function createFakeData(): Generator
    {
        if ($this->faker === null) {
            $this->faker = Factory::create();
        }

        return $this->faker;
    }

    /**
     * Возвращает путь до базовой папки для тестов.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected function getTempDir(): string
    {
        if ($this->tempDir === null) {
            $this->tempDir = sys_get_temp_dir();
            if (!$this->tempDir || !is_writable($this->tempDir)) {
                throw new RuntimeException(
                    "Can't find or write temporary folder: {$this->tempDir}"
                );
            }
            $this->tempDir .= DIRECTORY_SEPARATOR . 'fias_component';
            $this->removeDir($this->tempDir);
            if (!mkdir($this->tempDir, 0777, true)) {
                throw new RuntimeException(
                    "Can't create temporary folder: {$this->tempDir}"
                );
            }
        }

        return $this->tempDir;
    }

    /**
     * Создает тестовую директорию во временной папке и возвращает путь до нее.
     *
     * @param string $name
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected function getPathToTestDir(string $name = ''): string
    {
        if ($name === '') {
            $name = $this->createFakeData()->word;
        }

        $pathToFolder = $this->getTempDir() . DIRECTORY_SEPARATOR . $name;
        if (!mkdir($pathToFolder, 0777, true)) {
            throw new RuntimeException("Can't mkdir {$pathToFolder} folder");
        }

        return $pathToFolder;
    }

    /**
     * Создает тестовый файл во временной директории.
     *
     * @param string      $name
     * @param string|null $content
     *
     * @return string
     */
    protected function getPathToTestFile(string $name = '', ?string $content = null): string
    {
        if ($name === '') {
            $name = $this->createFakeData()->word . '.txt';
        }

        $pathToFile = $this->getTempDir() . DIRECTORY_SEPARATOR . $name;
        $content = $content === null ? $this->createFakeData()->word : $content;
        if (file_put_contents($pathToFile, $content) === false) {
            throw new RuntimeException("Can't create file {$pathToFile}");
        }

        return $pathToFile;
    }

    /**
     * Удаляет содержимое папки.
     *
     * @param string $folderPath
     */
    protected function removeDir(string $folderPath)
    {
        if (is_dir($folderPath)) {
            $it = new RecursiveDirectoryIterator(
                $folderPath,
                RecursiveDirectoryIterator::SKIP_DOTS
            );
            $files = new RecursiveIteratorIterator(
                $it,
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } elseif ($file->isFile()) {
                    unlink($file->getRealPath());
                }
            }
            rmdir($folderPath);
        }
    }

    /**
     * Удаляет тестовую директорию и все ее содержимое.
     */
    protected function tearDown(): void
    {
        if ($this->tempDir) {
            $this->removeDir($this->tempDir);
        }

        parent::tearDown();
    }

    /**
     * Проверяет, что мок реализует интерфейс объекта для записи в лог.
     *
     * @param mixed $logger
     *
     * @return LoggerInterface
     */
    protected function checkAndReturnLogger($logger): LoggerInterface
    {
        if (!($logger instanceof LoggerInterface)) {
            throw new RuntimeException('Wrong logger mock.');
        }

        return $logger;
    }

    /**
     * Проверяет, что мок реализует интерфейс объекта состояния.
     *
     * @param mixed $state
     *
     * @return State
     */
    protected function checkAndReturnState($state): State
    {
        if (!($state instanceof State)) {
            throw new RuntimeException('Wrong state mock.');
        }

        return $state;
    }

    /**
     * Проверяет, что мок реализует интерфейс объекта задачи.
     *
     * @param mixed $task
     *
     * @return Task
     */
    protected function checkAndReturnTask($task): Task
    {
        if (!($task instanceof Task)) {
            throw new RuntimeException('Wrong task mock.');
        }

        return $task;
    }

    /**
     * Проверяет, что мок реализует интерфейс объекта для управления версиями.
     *
     * @param mixed $versionManager
     *
     * @return VersionManager
     */
    protected function checkAndReturnVersionManager($versionManager): VersionManager
    {
        if (!($versionManager instanceof VersionManager)) {
            throw new RuntimeException('Wrong version manager mock.');
        }

        return $versionManager;
    }
}
