<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Parser;

use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\Parser\XmlParser;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Serializer\FiasSerializer;
use Liquetsoft\Fias\Component\Reader\XmlReader;
use SplFileInfo;

/**
 * Тест для объекта, который парсит данные из файлов xml/dbf.
 */
class BaseParserTest extends BaseCase
{
    /**
     * Проверяет, что объект парсит данные из xml.
     */
    public function testParseXml()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/test.xml');
        
        $serializer = new FiasSerializer;

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/StructureStatuses/StructureStatus'));

        $reader = new XmlReader;
        
        $parser = new XmlParser($reader, $serializer);
    
        $result = iterator_to_array($parser->getEntities($file, $descriptor, ParserObject::class));

        $this->assertSame($result[1]->getStrstatid(), 1);
        $this->assertSame($result[2]->getName(), 'Сооружение');
        $this->assertSame($result[3]->getShortname(), 'литер');
    }

    /**
     * Проверяет, что объект правильно парсит данные из xml, в котором нет нужных данных.
     */
    public function testParseUnexpectedXml()
    {
        $file = new SplFileInfo(__DIR__ . '/_fixtures/unexpected_test.xml');
        $serializer = new FiasSerializer;

        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getReaderParams')->will($this->returnValue('/StructureStatuses/StructureStatus'));

        $reader = new XmlReader;
        $parser = new XmlParser($reader, $serializer);
    
        $result = iterator_to_array($parser->getEntities($file, $descriptor, ParserObject::class));

        $this->assertSame([], $result);
    }
}
