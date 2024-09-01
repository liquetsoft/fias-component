<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Faker\Factory;
use Faker\Generator;
use Liquetsoft\Fias\Component\Exception\HttpTransportException;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransportResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Marvin255\FileSystemHelper\FileSystemException;
use Marvin255\FileSystemHelper\FileSystemFactory;
use Marvin255\FileSystemHelper\FileSystemHelperInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Базовый класс для всех тестов.
 */
abstract class BaseCase extends TestCase
{
    protected const STATUS_OK = 200;
    protected const STATUS_SERVER_ERROR = 500;

    private ?Generator $faker = null;

    private ?FileSystemHelperInterface $fs = null;

    private ?string $tempDir = null;

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
     * @throws \RuntimeException
     * @throws FileSystemException
     */
    protected function getTempDir(): string
    {
        if ($this->tempDir === null) {
            $this->tempDir = sys_get_temp_dir();
            if (!$this->tempDir || !is_writable($this->tempDir)) {
                throw new \RuntimeException(
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
     * @throws \RuntimeException
     * @throws FileSystemException
     */
    protected function getPathToTestDir(string $name = ''): string
    {
        if ($name === '') {
            $name = $this->createFakeData()->word();
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
            $name = $this->createFakeData()->word() . '.txt';
        }

        $pathToFile = $this->getTempDir() . \DIRECTORY_SEPARATOR . $name;
        $content = $content === null ? $this->createFakeData()->word() : $content;
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
        if ($this->tempDir !== null) {
            $this->fs()->remove($this->tempDir);
        }

        parent::tearDown();
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
        /** @var MockObject&State */
        $state = $this->getMockBuilder(State::class)->getMock();

        $state->method('getParameter')
            ->willReturnCallback(
                function (string $name) use ($params) {
                    return $params[$name] ?? null;
                }
            );

        if ($needCompleting !== null) {
            $expects = $needCompleting ? $this->once() : $this->never();
            $state->expects($expects)->method('complete');
        }

        return $state;
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return MockObject&T
     */
    protected function mock(string $className): MockObject
    {
        $mock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * Создает мок с ответом 200.
     *
     * @return HttpTransportResponse&MockObject
     */
    protected function createOkResponseMock(string|array $payload = '', bool $isJson = false): HttpTransportResponse
    {
        return $this->createResponseMock(self::STATUS_OK, [], $payload, $isJson);
    }

    /**
     * Создает мок с ответом 200.
     *
     * @return HttpTransportResponse&MockObject
     */
    protected function createBadResponseMock(): HttpTransportResponse
    {
        return $this->createResponseMock(self::STATUS_SERVER_ERROR);
    }

    /**
     * Создает для http ответа.
     *
     * @return HttpTransportResponse&MockObject
     */
    protected function createResponseMock(int $status, array $headers = [], string|array $payload = '', bool $isJson = false): HttpTransportResponse
    {
        $response = $this->mock(HttpTransportResponse::class);
        $response->method('getStatusCode')->willReturn($status);
        $response->method('isOk')->willReturn($status < 300 && $status >= 200);
        $response->method('getHeaders')->willReturn($headers);
        $response->method('isRangeSupported')->willReturn(($headers['accept-ranges'] ?? '') === 'bytes');
        $response->method('getContentLength')->willReturn((int) ($headers['content-length'] ?? 0));
        $response->method('getPayload')->willReturn(\is_string($payload) ? $payload : json_encode($payload));
        if (\is_array($payload) || $isJson) {
            $response->method('getJsonPayload')->willReturn($payload);
        } else {
            $response->method('getJsonPayload')->willThrowException(
                new HttpTransportException('Malformed json')
            );
        }

        return $response;
    }
}
