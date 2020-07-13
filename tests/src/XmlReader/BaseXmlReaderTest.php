<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Reader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Tests\EntityDescriptor\BaseEntityDescriptorTest;
use Liquetsoft\Fias\Component\EntityField\EntityField;
use Liquetsoft\Fias\Component\Exception\Exception;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Reader\BaseReader;
use SplFileInfo;

/**
 * Тест для объекта, который читает данные из xml файла.
 */
class BaseReaderTest extends BaseCase
{
    /**
     * Проверяет, что объект читает данные из xml.
     */
    public function testOpenUnexistedFileException()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/empty.xml');

        $reader = new BaseReader;
        $descriptor = new BaseEntityDescriptorTest;

        $this->expectException(InvalidArgumentException::class);
        $reader->open($file, $descriptor->createDescriptor(['xmlPath' => '/ActualStatuses/ActualStatus']));
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке начать чтение без открытого файла.
     */
    public function testReadNotOpenException()
    {
        $reader = new BaseReader;

        $this->expectException(Exception::class);
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
        $reader = new BaseReader;

        $this->expectException(Exception::class);
        $reader->current();
    }

    /**
     * Проверяет, что объект читает данные из xml.
     */
    public function testRead()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testRead.xml');

        $reader = new BaseReader;
        $descriptor = new BaseEntityDescriptorTest;

        $reader->open($file, $descriptor->createDescriptor(['xmlPath' => '/ActualStatuses/ActualStatus']));

        foreach ($reader as $key => $item) {
            $this->assertStringContainsString('ActualStatus', $item);
            $this->assertStringContainsString('ACTSTATID="' . $key . '', $item);
        }
     
        $reader->close();
    }

    /**
     * Проверяет, что объект читает данные из xml.
     */
    public function testGetType()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testRead.xml');

        $reader = new BaseReader;
        $descriptor = new BaseEntityDescriptorTest();
        $reader->open($file, $descriptor->createDescriptor());

        $this->assertSame($reader->getType(), 'xml');
        $reader->close();
    }

    /**
     * Проверяет, что объект правильно читает данные из xml, в котором нет нужных данных.
     */
    public function testReadEmpty()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testReadEmpty.xml');

        $reader = new BaseReader;
        $descriptor = new BaseEntityDescriptorTest;

        $reader->open($file, $descriptor->createDescriptor(['xmlPath' => '/ActualStatuses/ActualStatus']));
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

        $reader = new BaseReader;

        $descriptor = new BaseEntityDescriptorTest;

        $reader->open($file, $descriptor->createDescriptor(['xmlPath' => '/root/firstLevel/secondLevel/realItem']));
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertSame([
            '<realItem firstParam="real item 1 first param" secondParam="real item 1 second param"
                thirdParam="real item 1 third param" fake="real item 1 fake attr"/>',
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

        $reader = new BaseReader;
        $descriptor = new BaseEntityDescriptorTest;

        $reader->open($file, $descriptor->createDescriptor(['xmlPath' => '/root/qwe']));

        $this->expectException(Exception::class);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
    }
}
