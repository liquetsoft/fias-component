<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerImpl;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransport;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который получает ссылку на файл с архивом ФИАС
 * от сервиса информирования ФИАС.
 *
 * @internal
 */
final class FiasInformerImplTest extends BaseCase
{
    /**
     * Проверяет получение текущей версии.
     */
    public function testGetLatestVersion(): void
    {
        $latestUrl = 'https://test.test/latest';
        $versionId = 123;

        $response = $this->createOkResponseMock(['VersionId' => $versionId]);

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method('get')
            ->with($this->equalTo($latestUrl))
            ->willReturn($response);

        $informer = new FiasInformerImpl($transport, '', $latestUrl);
        $version = $informer->getLatestVersion();

        $this->assertSame($versionId, $version->getVersion());
    }

    /**
     * Проверяет, что объект перехватит исключение от http запроса.
     */
    public function testGetLatestVersionTransportException(): void
    {
        $message = 'test';

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method('get')
            ->willThrowException(new \RuntimeException($message));

        $informer = new FiasInformerImpl($transport);

        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage($message);
        $informer->getLatestVersion();
    }

    /**
     * Проверяет, что объект выбросит исключение при неверном статусе ответа.
     */
    public function testGetLatestVersionBadStatusException(): void
    {
        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method('get')
            ->willReturn($this->createBadResponseMock());

        $informer = new FiasInformerImpl($transport);

        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage((string) self::STATUS_SERVER_ERROR);
        $informer->getLatestVersion();
    }

    /**
     * Проверяет, что объект перехватит исключение при ошибке обработки json.
     */
    public function testGetLatestVersionJsonException(): void
    {
        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method('get')
            ->willReturn(
                $this->createOkResponseMock('test')
            );

        $informer = new FiasInformerImpl($transport);

        $this->expectException(FiasInformerException::class);
        $informer->getLatestVersion();
    }

    /**
     * Проверяет, что объект выбросит исключение, если в ответе будет не массив.
     */
    public function testGetLatestVersionResponseException(): void
    {
        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method('get')
            ->willReturn(
                $this->createOkResponseMock('test', true)
            );

        $informer = new FiasInformerImpl($transport);

        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage('Response from informer is malformed');
        $informer->getLatestVersion();
    }

    /**
     * Проверяет, что объект вернет следующую версию.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideGetNextVersion')]
    public function testGetNextVersion(?array $responseArray, int $current, int|\Exception|null $awaits): void
    {
        $allUrl = 'https://test.test/latest';

        $transport = $this->mock(HttpTransport::class);
        if ($responseArray === null) {
            $transport->expects($this->never())->method('get');
        } else {
            $response = $this->createOkResponseMock($responseArray);
            $transport->expects($this->once())
                ->method('get')
                ->with($this->equalTo($allUrl))
                ->willReturn($response);
        }

        if ($awaits instanceof \Exception) {
            $this->expectExceptionObject($awaits);
        }

        $informer = new FiasInformerImpl($transport, $allUrl);
        $nextVersion = $informer->getNextVersion($current);

        if ($awaits === null) {
            $this->assertNull($nextVersion);
        } elseif (!($awaits instanceof \Exception)) {
            $this->assertNotNull($nextVersion);
            $this->assertSame($awaits, $nextVersion->getVersion());
        }
    }

    public static function provideGetNextVersion(): array
    {
        return [
            'next version for int value' => [
                [
                    ['VersionId' => 1],
                    ['VersionId' => 2],
                    ['VersionId' => 3],
                    ['VersionId' => 4],
                ],
                2,
                3,
            ],
            'current version is latest' => [
                [
                    ['VersionId' => 1],
                    ['VersionId' => 2],
                ],
                2,
                null,
            ],
            'current version is undefined' => [
                [
                    ['VersionId' => 1],
                    ['VersionId' => 2],
                ],
                100,
                null,
            ],
            'no versions' => [
                [],
                2,
                null,
            ],
            'negative current version' => [
                null,
                -1,
                FiasInformerException::create('Version number must be more that 0'),
            ],
            'zero current version' => [
                null,
                0,
                FiasInformerException::create('Version number must be more that 0'),
            ],
        ];
    }

    /**
     * Проверяет, что объект выведет список всех версий.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideGetAllVersions')]
    public function testGetAllVersions(array $responseArray, array $awaits): void
    {
        $allUrl = 'https://test.test/latest';
        $response = $this->createOkResponseMock($responseArray);

        $transport = $this->mock(HttpTransport::class);
        $transport->expects($this->once())
            ->method('get')
            ->with($this->equalTo($allUrl))
            ->willReturn($response);

        $informer = new FiasInformerImpl($transport, $allUrl);
        $allVersions = $informer->getAllVersions();

        $this->assertCount(\count($awaits), $allVersions);
        foreach ($awaits as $key => $version) {
            $this->assertSame($version, $allVersions[$key]->getVersion());
        }
    }

    public static function provideGetAllVersions(): array
    {
        return [
            'sorted list' => [
                [
                    ['VersionId' => 1],
                    ['VersionId' => 2],
                ],
                [1, 2],
            ],
            'non sorted list' => [
                [
                    ['VersionId' => 9],
                    ['VersionId' => 3],
                ],
                [3, 9],
            ],
            'ignore non array values' => [
                [
                    'ignore',
                    ['VersionId' => 1],
                    ['VersionId' => 2],
                ],
                [1, 2],
            ],
        ];
    }
}
