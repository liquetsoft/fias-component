<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\XmlReader;

use Liquetsoft\Fias\Component\Exception\XmlException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderIterator;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderProviderImpl;

/**
 * Тест для объекта, который создает и возвращает php XMLReader для указанного файла.
 *
 * @internal
 */
class XmlReaderProviderImplTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно создаст \XmlReader.
     *
     * @dataProvider provideOpen
     */
    public function testOpen(string $path, string $xpath, \Exception $awaited = null): void
    {
        $file = new \SplFileInfo($path);

        if ($awaited instanceof \Exception) {
            $this->expectExceptionObject($awaited);
        }

        $provider = new XmlReaderProviderImpl();
        $reader = $provider->open($file, $xpath);

        if (!($awaited instanceof \Exception)) {
            $this->assertInstanceOf(XmlReaderIterator::class, $reader);
        }
    }

    public function provideOpen(): array
    {
        return [
            'correct data' => [
                __DIR__ . '/_fixtures/XmlReaderProviderImplTest/testOpen.xml',
                '/ActualStatuses/ActualStatus',
            ],
            'non existed file' => [
                __DIR__ . '/_fixtures/XmlReaderProviderImplTest/non_existed',
                '/ActualStatuses/ActualStatus',
                XmlException::create('/_fixtures/XmlReaderProviderImplTest/non_existed'),
            ],
        ];
    }
}
