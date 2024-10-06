<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Faker\Factory;
use Faker\Generator;
use Liquetsoft\Fias\Component\Exception\HttpTransportException;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransportResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
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
     * @return MockObject&State
     */
    protected function createStateMock(array $params = [], bool $isCompleted = false): State
    {
        $state = $this->mock(State::class);

        $state->expects($this->any())
            ->method('complete')
            ->willReturnCallback(
                fn (): mixed => $this->createStateMock($params, true)
            );

        $state->expects($this->any())
            ->method('setParameter')
            ->willReturnCallback(
                fn (StateParameter $param, mixed $value): mixed => $this->createStateMock(
                    array_merge($params, [$param->value => $value]),
                    $isCompleted
                )
            );

        $state->expects($this->any())
            ->method('getParameter')
            ->willReturnCallback(
                fn (StateParameter $param, mixed $default): mixed => $params[$param->value] ?? $default
            );

        $state->expects($this->any())
            ->method('getParameterInt')
            ->willReturnCallback(
                fn (StateParameter $param, int $default): int => (int) ($params[$param->value] ?? $default)
            );

        $state->expects($this->any())
            ->method('getParameterString')
            ->willReturnCallback(
                fn (StateParameter $param, string $default): string => (string) ($params[$param->value] ?? $default)
            );

        $state->expects($this->any())->method('isCompleted')->willReturn($isCompleted);

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
