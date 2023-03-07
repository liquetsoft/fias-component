<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponseFactory;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который представляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 *
 * @internal
 */
class InformerResponseFactoryTest extends BaseCase
{
    /**
     * Проверяет метод, который создает ответ по версии и ссылке.
     */
    public function testCreate(): void
    {
        $url = 'https://test.test/test';
        $version = 123;

        $res = InformerResponseFactory::create($version, $url);

        $this->assertSame($version, $res->getVersion());
        $this->assertSame($url, $res->getUrl());
    }

    /**
     * Проверяет метод, который создает ответ для полной версии по массиву из json ответа.
     *
     * @dataProvider provideCreateFullFromJson
     */
    public function testCreateFullFromJson(array $data, int|\Exception $awaitsVersion, string $awaitsUrl = ''): void
    {
        if ($awaitsVersion instanceof \Exception) {
            $this->expectExceptionObject($awaitsVersion);
        }

        $res = InformerResponseFactory::createFullFromJson($data);

        if (\is_int($awaitsVersion)) {
            $this->assertSame($awaitsVersion, $res->getVersion());
            $this->assertSame($awaitsUrl, $res->getUrl());
        }
    }

    public function provideCreateFullFromJson(): array
    {
        return [
            'correct array' => [
                [
                    'VersionId' => 123,
                    'GarXMLFullURL' => 'https://test.test/test',
                ],
                123,
                'https://test.test/test',
            ],
            'string version' => [
                [
                    'VersionId' => '123',
                    'GarXMLFullURL' => 'https://test.test/test',
                ],
                123,
                'https://test.test/test',
            ],
            'no version' => [
                [
                    'GarXMLFullURL' => 'https://test.test/test',
                ],
                new FiasInformerException('No version provided'),
            ],
            'no url' => [
                [
                    'VersionId' => 123,
                ],
                new FiasInformerException('No url provided'),
            ],
        ];
    }

    /**
     * Проверяет метод, который создает ответ для дельта версии по массиву из json ответа.
     *
     * @dataProvider provideCreateDeltaFromJson
     */
    public function testCreateDeltaFromJson(array $data, int|\Exception $awaitsVersion, string $awaitsUrl = ''): void
    {
        if ($awaitsVersion instanceof \Exception) {
            $this->expectExceptionObject($awaitsVersion);
        }

        $res = InformerResponseFactory::createDeltaFromJson($data);

        if (\is_int($awaitsVersion)) {
            $this->assertSame($awaitsVersion, $res->getVersion());
            $this->assertSame($awaitsUrl, $res->getUrl());
        }
    }

    public function provideCreateDeltaFromJson(): array
    {
        return [
            'correct array' => [
                [
                    'VersionId' => 123,
                    'GarXMLDeltaURL' => 'https://test.test/test',
                ],
                123,
                'https://test.test/test',
            ],
            'string version' => [
                [
                    'VersionId' => '123',
                    'GarXMLDeltaURL' => 'https://test.test/test',
                ],
                123,
                'https://test.test/test',
            ],
            'no version' => [
                [
                    'GarXMLDeltaURL' => 'https://test.test/test',
                ],
                new FiasInformerException('No version provided'),
            ],
            'no url' => [
                [
                    'VersionId' => 123,
                ],
                new FiasInformerException('No url provided'),
            ],
        ];
    }
}
