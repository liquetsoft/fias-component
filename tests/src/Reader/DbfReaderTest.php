<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Reader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityField\EntityField;
use Liquetsoft\Fias\Component\Exception\ReaderException;
use Liquetsoft\Fias\Component\Reader\DbfReader;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для объекта, который читает данные из файла.
 */
class DbfReaderTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, при попытке прочитать несуществующий файл.
     */
    public function testOpenUnexistedFileException()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/_.dbf');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('utf-8'));
        $reader = new DbfReader;

        $this->expectException(InvalidArgumentException::class);
        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке начать чтение без открытого файла.
     */
    public function testReadNotOpenException()
    {
        $reader = new DbfReader;

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
        $reader = new DbfReader;

        $this->expectException(ReaderException::class);
        $reader->current();
    }

    /**
     * Проверяет, что объект правильно определяет кодировки.
     */
    public function testEncoding()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testRead.dbf');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('CP866'));
        $reader = new DbfReader;

        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);
        $this->assertSame($reader->getEncoding(), 'CP866');
        $reader->close();

        $file = new SplFileInfo(__DIR__ . '/_fixtures/testReadUTF.dbf');
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('utf-8'));

        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);
        $this->assertSame($reader->getEncoding(), 'utf-8');
        $reader->close();
    }

    /**
     * Проверяет, что объект правильно получает столбцы.
     */
    public function testGetColumns()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testRead.dbf');

        $columns = ['strstatid', 'name', 'shortname'];
        $fields = [];
        foreach ($columns as $column) {
            $field = $this->getMockBuilder(EntityField::class)->getMock();
            $field->method('getName')->will($this->returnValue($column));
            $fields[] = $field;
        }

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getFields')->will($this->returnValue($fields));

        $reader = new DbfReader;

        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);
        $this->assertSame(array_keys($reader->getTableColumns()), $columns);
        $reader->close();
    }

    /**
     * Проверяет, что объект читает данные из dbf.
     */
    public function testRead()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testReadUTF.dbf');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('CP866'));

        $reader = new DbfReader;
        /** @var EntityDescriptor $descriptor */
        $this->assertSame($reader->open($file, $descriptor), true);
        $reader->close();
    }

    /**
     * Проверяет корректность типа файла.
     */
    public function testGetType()
    {
        $reader = new DbfReader;
        $this->assertSame($reader->getType(), 'dbf');
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке прочитать битый файл.
     */
    public function testReadException()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testReadException.dbf');

        $reader = new DbfReader;

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('CP866'));

        $this->expectException(ReaderException::class);
        /** @var EntityDescriptor $descriptor */
        $reader->open($file, $descriptor);
    }
}
