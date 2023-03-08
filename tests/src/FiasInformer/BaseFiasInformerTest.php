<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;
use Liquetsoft\Fias\Component\FiasInformer\BaseFiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Tests\HttpTransportCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который получает ссылку на файл с архивом ФИАС
 * от сервиса информирования ФИАС.
 *
 * @internal
 */
class BaseFiasInformerTest extends HttpTransportCase
{
    /**
     * Проверяет получение текущей версии.
     */
    public function testGetLatestVersion(): void
    {
        $latestUrl = 'https://test.test/latest';
        $versionId = 123;

        $response = $this->createOkResponseMock(['VersionId' => $versionId]);
        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('get')
            ->with($this->equalTo($latestUrl))
            ->willReturn($response);

        $informer = new BaseFiasInformer($transport, '', $latestUrl);
        $version = $informer->getLatestVersion();

        $this->assertSame($versionId, $version->getVersion());
    }

    /**
     * Проверяет, что объект перехватит исключение от http запроса.
     */
    public function testGetLatestVersionTransportException(): void
    {
        $message = 'test';

        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('get')
            ->willThrowException(new \RuntimeException($message));

        $informer = new BaseFiasInformer($transport);

        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage($message);
        $informer->getLatestVersion();
    }

    /**
     * Проверяет, что объект выбросит исключение при неверном статусе ответа.
     */
    public function testGetLatestVersionBadStatusException(): void
    {
        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('get')
            ->willReturn($this->createBadResponseMock());

        $informer = new BaseFiasInformer($transport);

        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage((string) self::STATUS_SERVER_ERROR);
        $informer->getLatestVersion();
    }

    /**
     * Проверяет, что объект перехватит исключение при ошибке обработки json.
     */
    public function testGetLatestVersionJsonException(): void
    {
        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('get')
            ->willReturn($this->createOkResponseMock('test'));

        $informer = new BaseFiasInformer($transport);

        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE_JSON);
        $informer->getLatestVersion();
    }

    /**
     * Проверяет, что объект выбросит исключение, если в ответе будет не массив.
     */
    public function testGetLatestVersionResponseException(): void
    {
        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('get')
            ->willReturn($this->createOkResponseMock('test', true));

        $informer = new BaseFiasInformer($transport);

        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage('Response from informer is malformed');
        $informer->getLatestVersion();
    }

    /**
     * Проверяет, что объект вернет следующую версию.
     *
     * @dataProvider provideGetNextVersion
     */
    public function testGetNextVersion(array $responseArray, int|InformerResponse $current, ?int $awaits): void
    {
        $allUrl = 'https://test.test/latest';
        $response = $this->createOkResponseMock($responseArray);

        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('get')
            ->with($this->equalTo($allUrl))
            ->willReturn($response);

        $informer = new BaseFiasInformer($transport, $allUrl);
        $nextVersion = $informer->getNextVersion($current);

        if ($awaits === null) {
            $this->assertNull($nextVersion);
        } else {
            $this->assertNotNull($nextVersion);
            $this->assertSame($awaits, $nextVersion->getVersion());
        }
    }

    public function provideGetNextVersion(): array
    {
        /** @var MockObject&InformerResponse */
        $informerResponseMock = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResponseMock->method('getVersion')->willReturn(2);

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
            'next version for informer response value' => [
                [
                    ['VersionId' => 1],
                    ['VersionId' => 2],
                    ['VersionId' => 3],
                    ['VersionId' => 4],
                ],
                $informerResponseMock,
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
        ];
    }

    /**
     * Проверяет, что объект выведет список всех версий.
     *
     *  @dataProvider provideGetAllVersions
     */
    public function testGetAllVersions(array $responseArray, array $awaits): void
    {
        $allUrl = 'https://test.test/latest';
        $response = $this->createOkResponseMock($responseArray);

        $transport = $this->createTransportMock();
        $transport->expects($this->once())
            ->method('get')
            ->with($this->equalTo($allUrl))
            ->willReturn($response);

        $informer = new BaseFiasInformer($transport, $allUrl);
        $allVersions = $informer->getAllVersions();

        $this->assertCount(\count($awaits), $allVersions);
        foreach ($awaits as $key => $version) {
            $this->assertSame($version, $allVersions[$key]->getVersion());
        }
    }

    public function provideGetAllVersions(): array
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
