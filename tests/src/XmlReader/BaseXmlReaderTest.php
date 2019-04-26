<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Unpacker;

use Liquetsoft\Fias\Component\XmlReader\BaseXmlReader;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Exception\XmlException;
use SplFileInfo;
use InvalidArgumentException;

/**
 * Тест для объекта, который читает данные из xml файла.
 */
class BaseXmlReaderTest extends BaseCase
{
    /**
     * Проверяет, что объект читает данные из xml.
     */
    public function testOpenUnexistedFileException()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/empty.xml');

        $reader = new BaseXmlReader;

        $this->expectException(InvalidArgumentException::class);
        $reader->open($file, '/ActualStatuses/ActualStatus');
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке начать чтение без открытого файла.
     */
    public function testReadNotOpenException()
    {
        $reader = new BaseXmlReader;

        $this->expectException(XmlException::class);
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
        $reader = new BaseXmlReader;

        $this->expectException(XmlException::class);
        $reader->current();
    }

    /**
     * Проверяет, что объект читает данные из xml.
     */
    public function testRead()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testRead.xml');

        $reader = new BaseXmlReader;
        $reader->open($file, '/ActualStatuses/ActualStatus');
        $result = [];
        foreach ($reader as $key => $item) {
        }
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertSame(
            [
                0 => '<ActualStatus ACTSTATID="0" NAME="Не актуальный"/>',
                1 => '<ActualStatus ACTSTATID="1" NAME="Актуальный"/>',
            ],
            $result
        );
    }

    /**
     * Проверяет, что объект правильно читает данные из xml, в котором нет нужных данных.
     */
    public function testReadEmpty()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testReadEmpty.xml');

        $reader = new BaseXmlReader;
        $reader->open($file, '/ActualStatuses/ActualStatus');
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

        $reader = new BaseXmlReader;
        $reader->open($file, '/root/firstLevel/secondLevel/realItem');
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertSame([
            '<realItem firstParam="real item 1 first param" secondParam="real item 1 second param" thirdParam="real item 1 third param" fake="real item 1 fake attr"/>',
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

        $reader = new BaseXmlReader;
        $reader->open($file, '/root/qwe');

        $this->expectException(XmlException::class);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
    }
}
