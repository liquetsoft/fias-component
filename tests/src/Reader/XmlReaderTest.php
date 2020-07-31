<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Reader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\ReaderException;
use Liquetsoft\Fias\Component\Reader\XmlReader;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для объекта, который читает данные из файла.
 */
class XmlReaderTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, при попытке прочитать несуществующий файл.
     */
    public function testOpenUnexistedFileException()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/empty.xml');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/ActualStatuses/ActualStatus'));

        $reader = new XmlReader;

        $this->expectException(InvalidArgumentException::class);
        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке начать чтение без открытого файла.
     */
    public function testReadNotOpenException()
    {
        $reader = new XmlReader;

        $this->expectException(ReaderException::class);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке начать чтение без открытого файла.
     */
    public function testReadNotOpenExceptionIterator()
    {
        $reader = new XmlReader;

        $this->expectException(ReaderException::class);
        $reader->current();
    }

    /**
     * Проверяет, что объект читает данные из xml.
     */
    public function testRead()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testRead.xml');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/ActualStatuses/ActualStatus'));

        $reader = new XmlReader;

        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);

        foreach ($reader as $key => $item) {
            $this->assertStringContainsString('ActualStatus', $item);
            $this->assertStringContainsString('ACTSTATID="' . $key . '', $item);
        }

        $reader->close();
    }

    /**
     * Проверяет корректность типа файла.
     */
    public function testGetType()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testRead.xml');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/ActualStatuses/ActualStatus'));

        $reader = new XmlReader;
        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);

        $this->assertSame($reader->getType(), 'xml');
        $reader->close();
    }

    /**
     * Проверяет, что объект правильно читает данные из xml, в котором нет нужных данных.
     */
    public function testReadEmpty()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testReadEmpty.xml');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/ActualStatuses/ActualStatus'));

        $reader = new XmlReader;

        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertSame([], $result);
    }

    /**
     * Проверяет, что объект правильно читает данные из xml, в котором много отхождений
     * от ожидаемого формата.
     */
    public function testReadMessyFile()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testReadMessyFile.xml');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/root/firstLevel/secondLevel/realItem'));

        $reader = new XmlReader;

        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertSame([
            '<realItem firstParam="real item 1 first param" secondParam="real item 1 second param" '
                . 'thirdParam="real item 1 third param" fake="real item 1 fake attr"/>',
            '<realItem firstParam="real item 2 first param" secondParam="real item 2 second param"/>',
            '<realItem fake="real item 3 fake attr"/>',
        ], $result);
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке прочитать битый файл.
     */
    public function testReadException()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testReadException.xml');

        $reader = new XmlReader;

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/root/qwe'));

        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);

        $this->expectException(ReaderException::class);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
    }
}
