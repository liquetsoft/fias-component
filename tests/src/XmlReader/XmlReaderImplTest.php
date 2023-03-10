<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\XmlReader;

use Liquetsoft\Fias\Component\Exception\XmlException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderImpl;

/**
 * Тест для объекта, который читает данные из xml файла.
 *
 * @internal
 */
class XmlReaderImplTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если указан пустой xpath.
     */
    public function testOpenEmptyXpathException(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testOpen.xml');
        $xpath = '';

        $reader = new XmlReaderImpl();

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage("Xpath parameter can't be empty and must start with '/'");
        $reader->open($file, $xpath);
    }

    /**
     * Проверяет, что объект выбросит исключение, если xpath начинается не со слэша.
     */
    public function testOpenNoLeadingSlashXpathException(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testOpen.xml');
        $xpath = 'ActualStatuses/ActualStatus';

        $reader = new XmlReaderImpl();

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage("Xpath parameter can't be empty and must start with '/'");
        $reader->open($file, $xpath);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в xpath задан только слэш.
     */
    public function testOpenOnlySlashXpathException(): void
    {
        $path = __DIR__ . '/_fixtures/testOpen.xml';
        $file = new \SplFileInfo($path);
        $xpath = '/';

        $reader = new XmlReaderImpl();

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage("Xpath parameter can't be empty and must start with '/'");
        $reader->open($file, $xpath);
    }

    /**
     * Проверяет, что объект читает данные из xml.
     */
    public function testIterator(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testIterator.xml');
        $xpath = '  /ActualStatuses/ActualStatus  ';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertCount(3, $result);
        foreach ($result as $key => $item) {
            $this->assertStringContainsString('ActualStatus', $item);
            $this->assertStringContainsString('ACTSTATID="' . $key . '', $item);
        }
    }

    /**
     * Проверяет, что объект читает данные из xml, при повторном использовании в цикле.
     */
    public function testIteratorWithRewind(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testIterator.xml');
        $xpath = '/ActualStatuses/ActualStatus';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
            if ($key >= 1) {
                break;
            }
        }
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertCount(3, $result);
        foreach ($result as $key => $item) {
            $this->assertStringContainsString('ActualStatus', $item);
            $this->assertStringContainsString('ACTSTATID="' . $key . '', $item);
        }
    }

    /**
     * Проверяет, что объект правильно читает данные из xml, в котором нет нужных данных.
     */
    public function testIteratorEmptyFile(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testIteratorEmptyFile.xml');
        $xpath = '/ActualStatuses/ActualStatus';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertSame([], $result);
    }

    /**
     * Проверяет, что объект правильно читает данные из xml, в котором много отличий
     * от ожидаемого формата.
     */
    public function testIteratorMessyFile(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testIteratorMessyFile.xml');
        $xpath = '/root/firstLevel/secondLevel/realItem';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);
        $result = [];
        foreach ($reader as $key => $item) {
            $result[$key] = $item;
        }
        $reader->close();

        $this->assertSame(
            [
                '<realItem firstParam="real item 1 first param" secondParam="real item 1 second param" thirdParam="real item 1 third param" fake="real item 1 fake attr"/>',
                '<realItem firstParam="real item 2 first param" secondParam="real item 2 second param"/>',
                '<realItem fake="real item 3 fake attr"/>',
            ],
            $result
        );
    }

    /**
     * Проверяет, что объект выбросит исключение при попытке получить данные без открытого файла.
     */
    public function testIteratorNotOpenException(): void
    {
        $reader = new XmlReaderImpl();

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage("File wasn't opened");
        foreach ($reader as $item) {
            continue;
        }
    }

    /**
     * Проверяет, что объект выбросит исключение при попытке прочитать данные после закрытия файла.
     */
    public function testIteratorClosedFileException(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testIterator.xml');
        $xpath = '/ActualStatuses/ActualStatus';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);
        $reader->close();

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage("File wasn't opened");
        foreach ($reader as $item) {
            continue;
        }
    }

    /**
     * Проверяет, что объект выбросит исключение при попытке открыть несуществующий файл.
     */
    public function testIteratorNonExistedFileException(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/non_existed');
        $xpath = '/ActualStatuses/ActualStatus';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage("Can't open file");
        foreach ($reader as $item) {
            continue;
        }
    }

    /**
     * Проверяет, что объект выбросит исключение при попытке прочитать испорченный файл.
     */
    public function testIteratorMalformedFileException(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testIteratorMalformedFileException.xml');
        $xpath = '/ActualStatuses/ActualStatus';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('parser error : Document is empty');
        foreach ($reader as $item) {
            continue;
        }
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке прочитать битый файл.
     */
    public function testIteratorInterruptedFileException(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testIteratorInterruptedFileException.xml');
        $xpath = '/root/qwe';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('parser error : Extra content at the end of the document');
        foreach ($reader as $item) {
            continue;
        }
    }

    /**
     * Проверяет, что объект выбросит исключение, при попытке использовать методы итератора без правильно инициализации.
     */
    public function testIteratorDirectAccessToMethodsException(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/_fixtures/testIterator.xml');
        $xpath = '/ActualStatuses/ActualStatus';

        $reader = new XmlReaderImpl();
        $reader->open($file, $xpath);

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage("Iterator wasn't initialized");
        $reader->next();
    }
}
