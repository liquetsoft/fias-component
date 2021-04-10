<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Faker\Factory;
use Faker\Generator;
use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Liquetsoft\Fias\Component\VersionManager\VersionManager;
use Marvin255\FileSystemHelper\FileSystemException;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelperInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
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
     * @var FileSystemHelperInterface|null
     */
    private $fs;

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
     * Возвращает объект для работы с файловой системой.
     *
     * @return FileSystemHelperInterface
     */
    public function fs(): FileSystemHelperInterface
    {
        if ($this->fs === null) {
            $this->fs = FileSystemFactory::create();
        }

        return $this->fs;
    }

    /**
     * Возвращает путь до базовой папки для тестов.
     *
     * @return string
     *
     * @throws RuntimeException
     * @throws FileSystemException
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
            $this->tempDir .= \DIRECTORY_SEPARATOR . 'fias_component';
            $this->fs()->mkdirIfNotExist($this->tempDir);
            $this->fs()->emptyDir($this->tempDir);
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
     * @throws FileSystemException
     */
    protected function getPathToTestDir(string $name = ''): string
    {
        if ($name === '') {
            $name = $this->createFakeData()->word;
        }

        $pathToFolder = $this->getTempDir() . \DIRECTORY_SEPARATOR . $name;

        $this->fs()->mkdir($pathToFolder);

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

        $pathToFile = $this->getTempDir() . \DIRECTORY_SEPARATOR . $name;
        $content = $content === null ? $this->createFakeData()->word : $content;
        if (file_put_contents($pathToFile, $content) === false) {
            throw new RuntimeException("Can't create file {$pathToFile}");
        }

        return $pathToFile;
    }

    /**
     * Удаляет тестовую директорию и все ее содержимое.
     */
    protected function tearDown(): void
    {
        if ($this->tempDir) {
            $this->fs()->remove($this->tempDir);
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
     * Создает мок для объекта состояния.
     *
     * @param array     $params
     * @param bool|null $needCompleting
     *
     * @return State
     */
    protected function createDefaultStateMock(array $params = [], ?bool $needCompleting = null): State
    {
        $state = $this->getMockBuilder(State::class)->getMock();

        $state->method('getParameter')
            ->will(
                $this->returnCallback(
                    function ($name) use ($params) {
                        return $params[$name] ?? null;
                    }
                )
            );

        if ($needCompleting !== null) {
            $expects = $needCompleting ? $this->once() : $this->never();
            $state->expects($expects)->method('complete');
        }

        if (!($state instanceof State)) {
            throw new RuntimeException('Wrong state mock.');
        }

        return $state;
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

    /**
     * Проверяет, что мок реализует интерфейс объекта для распаковки архива.
     *
     * @param mixed $unpack
     *
     * @return Unpacker
     */
    protected function checkAndReturnUnpack($unpack): Unpacker
    {
        if (!($unpack instanceof Unpacker)) {
            throw new RuntimeException('Wrong unpack mock.');
        }

        return $unpack;
    }

    /**
     * Проверяет, что мок реализует интерфейс объекта для загрузки файлов.
     *
     * @param mixed $downloader
     *
     * @return Downloader
     */
    protected function checkAndReturnDownloader($downloader): Downloader
    {
        if (!($downloader instanceof Downloader)) {
            throw new RuntimeException('Wrong downloader mock.');
        }

        return $downloader;
    }
}
