<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Parser;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityField\EntityField;
use Liquetsoft\Fias\Component\Exception\ParserException;
use Liquetsoft\Fias\Component\Parser\DbfParser;
use Liquetsoft\Fias\Component\Reader\DbfReader;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для объекта, который парсит данные из файлов dbf.
 */
class DbfParserTest extends BaseCase
{
    /**
     * Проверяет, что объект парсит данные dbf в кодировке CP-866.
     */
    public function testParseDbf()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/test.dbf');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('CP866'));

        $reader = new DbfReader;
        $parser = new DbfParser($reader);

        /** @var EntityDescriptor $descriptor */
        $result = iterator_to_array($parser->getEntities($file, $descriptor, DbfParserObject::class));

        $this->assertSame($result[1]->strstatid, 1);
        $this->assertSame($result[2]->name, 'Сооружение');
        $this->assertSame($result[3]->shortname, 'литер');
    }

    /**
     * Проверяет, что объект парсит данные dbf в кодировке UTF-8.
     */
    public function testParseDbfUtf()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/testUTF.dbf');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('utf-8'));

        $reader = new DbfReader;
        $parser = new DbfParser($reader);

        /** @var EntityDescriptor $descriptor */
        $result = iterator_to_array($parser->getEntities($file, $descriptor, DbfParserObject::class));

        $this->assertSame($result[1]->strstatid, 1);
        $this->assertSame($result[2]->name, 'ଡିଜାଇନ୍ |');
        $this->assertSame($result[3]->shortname, 'ਪੰਜਾਬੀ');
    }

    /**
     * Проверяет, что объект правильно парсит нужные колонки из dbf.
     */
    public function testParseColumnDbf()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/test.dbf');
        // Колонки, которые будут парситься
        $columns = ['strstatid', 'name'];
        $fields = [];
        foreach ($columns as $column) {
            $field = $this->getMockBuilder(EntityField::class)->getMock();
            $field->method('getName')->will($this->returnValue($column));
            $fields[] = $field;
        }

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getFields')->will($this->returnValue($fields));
        $descriptor->method('getReaderParams')->will($this->returnValue('CP866'));

        $reader = new DbfReader;
        $parser = new DbfParser($reader);

        /** @var EntityDescriptor $descriptor */
        $result = iterator_to_array($parser->getEntities($file, $descriptor, DbfParserObject::class));

        $this->assertSame($result[1]->strstatid, 1);
        $this->assertSame($result[2]->name, 'Сооружение');

        // Исключение при обращении к колонке, которая не была указана в списке
        $this->expectException(ParserException::class);
        try {
            $this->assertSame($result[3]->shortname, 'литер');
        } catch (\Throwable $e) {
            throw new ParserException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Проверяет, что объект правильно парсит пустой dbf.
     */
    public function testParseEmptyDbf()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/empty.dbf');

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('utf-8'));

        $reader = new DbfReader;
        $parser = new DbfParser($reader);

        /** @var EntityDescriptor $descriptor */
        $result = iterator_to_array($parser->getEntities($file, $descriptor, DbfParserObject::class));

        $this->assertSame($result, []);
    }
}

/**
 * Класс для проверки парсера.
 */
class DbfParserObject
{
    public $strstatid = 0;
    public $name = '';
    public $shortname = '';
}
