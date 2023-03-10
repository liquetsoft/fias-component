<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponseFactory;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который представляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 *
 * @internal
 */
class FiasInformerResponseFactoryTest extends BaseCase
{
    /**
     * Проверяет метод, который создает ответ по массиву из json ответа.
     *
     * @dataProvider provideCreateFromJson
     */
    public function testCreateFromJson(
        array $data,
        int|\Exception $awaitsVersion,
        string $awaitsFullUrl = '',
        string $awaitsDeltalUrl = ''
    ): void {
        if ($awaitsVersion instanceof \Exception) {
            $this->expectExceptionObject($awaitsVersion);
        }

        $res = FiasInformerResponseFactory::createFromJson($data);

        if (\is_int($awaitsVersion)) {
            $this->assertSame($awaitsVersion, $res->getVersion());
            $this->assertSame($awaitsFullUrl, $res->getFullUrl());
            $this->assertSame($awaitsDeltalUrl, $res->getDeltaUrl());
        }
    }

    public function provideCreateFromJson(): array
    {
        return [
            'correct array' => [
                [
                    'VersionId' => 123,
                    'GarXMLFullURL' => 'https://test.test/full',
                    'GarXMLDeltaURL' => 'https://test.test/delta',
                ],
                123,
                'https://test.test/full',
                'https://test.test/delta',
            ],
            'string version' => [
                [
                    'VersionId' => '123',
                    'GarXMLFullURL' => 'https://test.test/full',
                    'GarXMLDeltaURL' => 'https://test.test/delta',
                ],
                123,
                'https://test.test/full',
                'https://test.test/delta',
            ],
            'no url' => [
                [
                    'VersionId' => 123,
                ],
                123,
                '',
                '',
            ],
            'no version' => [
                [
                    'GarXMLFullURL' => 'https://test.test/full',
                    'GarXMLDeltaURL' => 'https://test.test/delta',
                ],
                new FiasInformerException('Version must be more than zero'),
            ],
            'malformed delta url' => [
                [
                    'VersionId' => 123,
                    'GarXMLFullURL' => 'https://test.test/full',
                    'GarXMLDeltaURL' => 123,
                ],
                new FiasInformerException("String '123' is not an url"),
            ],
            'malformed full url' => [
                [
                    'VersionId' => 123,
                    'GarXMLFullURL' => 123,
                    'GarXMLDeltaURL' => 'https://test.test/delta',
                ],
                new FiasInformerException("String '123' is not an url"),
            ],
        ];
    }
}
