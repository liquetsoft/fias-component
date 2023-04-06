<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\XmlReader;

use Liquetsoft\Fias\Component\Exception\XmlException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderIteratorImpl;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderProviderImpl;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тест для объекта, который читает данные из xml файла.
 *
 * @internal
 */
class XmlReaderIteratorImplTest extends BaseCase
{
    /**
     * Проверяет, что объект проведет валидацию при создании.
     *
     * @dataProvider provideConstructExcetion
     */
    public function testConstructExcetion(\XMLReader $reader, string $xpath, \Exception $awaited): void
    {
        $this->expectExceptionObject($awaited);
        new XmlReaderIteratorImpl($reader, $xpath);
    }

    public function provideConstructExcetion(): array
    {
        return [
            'empty xpath' => [
                $this->createXmlReaderMock(),
                '',
                XmlException::create("Xpath parameter can't be empty and must start with '/'"),
            ],
            'no leading slash in xpath' => [
                $this->createXmlReaderMock(),
                'ActualStatuses/ActualStatus',
                XmlException::create("Xpath parameter can't be empty and must start with '/'"),
            ],
            'only slash xpath' => [
                $this->createXmlReaderMock(),
                '/',
                XmlException::create("Xpath parameter can't be empty and must start with '/'"),
            ],
        ];
    }

    /**
     * Проверяет, что объект читает данные из xml.
     *
     * @dataProvider provideIterator
     */
    public function testIterator(string $path, string $xpath, array|\Exception $awaited): void
    {
        $reader = $this->createXmlReader($path);
        $iterator = new XmlReaderIteratorImpl($reader, $xpath);

        if ($awaited instanceof \Exception) {
            $this->expectException(\get_class($awaited));
            $this->expectExceptionMessage($awaited->getMessage());
        }

        $result = [];
        foreach ($iterator as $key => $item) {
            $result[$key] = $item;
        }
        $iterator->close();

        if (!($awaited instanceof \Exception)) {
            $this->assertSame($awaited, $result);
        }
    }

    public function provideIterator(): array
    {
        return [
            'correct file' => [
                __DIR__ . '/_fixtures/XmlReaderIteratorImplTest/testIterator.xml',
                '/ActualStatuses/ActualStatus',
                [
                    '<ActualStatus ACTSTATID="0" NAME="Не актуальный &lt;&lt;A&gt;&gt;"/>',
                    '<ActualStatus ACTSTATID="1" NAME="Актуальный"/>',
                    '<ActualStatus ACTSTATID="2" NAME="3-й &quot;А&quot;"/>',
                ],
            ],
            'xpath with leading and trailing spaces' => [
                __DIR__ . '/_fixtures/XmlReaderIteratorImplTest/testIterator.xml',
                '   /ActualStatuses/ActualStatus   ',
                [
                    '<ActualStatus ACTSTATID="0" NAME="Не актуальный &lt;&lt;A&gt;&gt;"/>',
                    '<ActualStatus ACTSTATID="1" NAME="Актуальный"/>',
                    '<ActualStatus ACTSTATID="2" NAME="3-й &quot;А&quot;"/>',
                ],
            ],
            'empty file' => [
                __DIR__ . '/_fixtures/XmlReaderIteratorImplTest/testIteratorEmptyFile.xml',
                '/ActualStatuses/ActualStatus',
                [],
            ],
            'messy file' => [
                __DIR__ . '/_fixtures/XmlReaderIteratorImplTest/testIteratorMessyFile.xml',
                '/root/firstLevel/secondLevel/realItem',
                [
                    '<realItem firstParam="real item 1 first param" secondParam="real item 1 second param" thirdParam="real item 1 third param" fake="real item 1 fake attr"/>',
                    '<realItem firstParam="real item 2 first param" secondParam="real item 2 second param"/>',
                    '<realItem fake="real item 3 fake attr"/>',
                ],
            ],
            'malformed file' => [
                __DIR__ . '/_fixtures/XmlReaderIteratorImplTest/testIteratorMalformedFileException.xml',
                '/ActualStatuses/ActualStatus',
                XmlException::create('parser error : Document is empty'),
            ],
            'interrupted file' => [
                __DIR__ . '/_fixtures/XmlReaderIteratorImplTest/testIteratorInterruptedFileException.xml',
                '/root/qwe',
                XmlException::create('parser error : Extra content at the end of the document'),
            ],
        ];
    }

    /**
     * Проверяет, что объект выбросит исключение при повторной попытке использовать итератор.
     */
    public function testIteratorRewindException(): void
    {
        $xpath = '/ActualStatuses/ActualStatus';
        $reader = $this->createXmlReader(__DIR__ . '/_fixtures/XmlReaderIteratorImplTest/testIterator.xml');

        $iterator = new XmlReaderIteratorImpl($reader, $xpath);

        $iterator->rewind();

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage("This iterator can't be rewinded");
        $iterator->rewind();
    }

    /**
     * Проверяет, что объект закроет вложенный XmlReader.
     */
    public function testClose(): void
    {
        $reader = $this->createXmlReaderMock();
        $reader->expects($this->once())->method('close');

        $xpath = '/ActualStatuses/ActualStatus';

        $iterator = new XmlReaderIteratorImpl($reader, $xpath);
        $iterator->close();
    }

    /**
     * Проверяет, что объект закроет вложенный XmlReader, если итератор дойдет до конца.
     */
    public function testCloseOnIteratorComplete(): void
    {
        $reader = $this->createXmlReaderMock();
        $reader->method('read')->willReturn(false);
        $reader->expects($this->once())->method('close');

        $xpath = '/ActualStatuses/ActualStatus';

        $iterator = new XmlReaderIteratorImpl($reader, $xpath);
        foreach ($iterator as $item) {
        }
    }

    /**
     * Создает мок для XMLReader.
     *
     * @return \XMLReader&MockObject
     */
    private function createXmlReaderMock(): \XMLReader
    {
        /** @var \XMLReader&MockObject */
        $reader = $this->getMockBuilder(\XMLReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $reader;
    }

    /**
     * Возвращает реальный объект XMLReader для указанного файла.
     */
    private function createXmlReader(string $path): \XMLReader
    {
        /** @var \XMLReader */
        $reader = \XMLReader::open(
            $path,
            XmlReaderProviderImpl::XML_READER_CHARSET,
            XmlReaderProviderImpl::XML_READER_OPTIONS
        );

        return $reader;
    }
}
