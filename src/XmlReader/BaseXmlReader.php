<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use Liquetsoft\Fias\Component\Exception\XmlException;
use Liquetsoft\Fias\Component\XmlReader\XmlReader as XmlReaderInterface;

/**
 * Объект, который читает данные из xml файла с помощью XmlReader.
 */
final class BaseXmlReader implements XmlReaderInterface
{
    private const XML_READER_CHARSET = 'UTF-8';
    private const XML_READER_PARAMS = \LIBXML_COMPACT | \LIBXML_NONET | \LIBXML_NOBLANKS;

    /**
     * Файл, который открыт в данный момент.
     */
    private ?\SplFileInfo $file = null;

    /**
     * Xpath, по которому следует искать данные.
     */
    private string $xpath = '';

    /**
     * Объект XMLReader для чтения документа.
     */
    private ?\XMLReader $reader = null;

    /**
     * Текущее смещение внутри массива.
     */
    private int $position = 0;

    /**
     * Флаг, который указывает, что данные были прочитаны в буфер.
     */
    private bool $isBufferFull = false;

    /**
     * Массив с буфером, для isValid и current.
     */
    private ?string $buffer = null;

    /**
     * {@inheritdoc}
     */
    public function open(\SplFileInfo $file, string $xpath): bool
    {
        if (!$file->isFile() || !$file->isReadable()) {
            throw XmlException::create("File '%s' isn't readable or doesn't exist", $file->getPathname());
        }

        $this->file = $file;
        $this->xpath = $xpath;

        return $this->seekXmlPath();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->unsetReader();
        $this->file = null;
        $this->xpath = '';
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->buffer = null;
        $this->isBufferFull = false;
        $this->seekXmlPath();
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function current(): ?string
    {
        if (!$this->isBufferFull) {
            $this->isBufferFull = true;
            $this->buffer = $this->getLine();
        }

        return $this->buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function next(): void
    {
        ++$this->position;
        $this->isBufferFull = true;
        $this->buffer = $this->getLine();
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function valid(): bool
    {
        if (!$this->isBufferFull) {
            $this->isBufferFull = true;
            $this->buffer = $this->getLine();
        }

        return $this->buffer !== null;
    }

    /**
     * Возвращает строку из файла, соответствующую элементу, или null, если разбор
     * файла завершен.
     *
     * @throws XmlException
     */
    private function getLine(): ?string
    {
        if (!$this->reader) {
            throw XmlException::create('Reader and xpath must be set before reading');
        }

        $return = null;
        $arPath = explode('/', $this->xpath);
        $nameFilter = array_pop($arPath);
        $currentDepth = $this->reader->depth;

        try {
            $this->skipUselessXml($nameFilter, $currentDepth);
            // мы можем выйти из цикла, если найдем нужный элемент
            // или попадем на уровень выше - проверяем, что нашли нужный
            if ($nameFilter === $this->reader->name) {
                $return = $this->reader->readOuterXml();
                // нужно передвинуть указатель, чтобы дважды не прочитать
                // один и тот же элемент
                $this->reader->next();
            }
        } catch (\Throwable $e) {
            $fileName = $this->file?->getPathname() ?? '';
            $message = "Error while parsing xml '{$fileName}' by '{$this->xpath}' path";
            throw new XmlException(message: $message, previous: $e);
        }

        return $return;
    }

    /**
     * Пропускает все xml элементы в текущем ридере, у которых имя или вложенность
     * не совпадают с указанным параметром.
     */
    private function skipUselessXml(string $nodeName, int $nodeDepth): void
    {
        while (
            $this->reader
            && $this->reader->depth === $nodeDepth
            && $nodeName !== $this->reader->name
            && $this->reader->next()
        ) {
            continue;
        }
    }

    /**
     * Ищет узел заданный в описании сущности, прежде, чем начать перебор
     * элементов.
     *
     * Если собранный путь лежит в начале строки, которую мы ищем,
     * то продолжаем поиск.
     * Если собранный путь совпадает с тем, что мы ищем,
     * то выходим из цикла.
     * Если путь не совпадает и не лежит в начале строки,
     * то пропускаем данный узел со всеми вложенными деревьями.
     *
     * @throws XmlException
     */
    private function seekXmlPath(): bool
    {
        $reader = $this->resetReader();

        $path = trim($this->xpath, '/');
        $currentPath = [];
        $isCompleted = false;
        $readResult = $reader->read();

        while ($readResult) {
            array_push($currentPath, $reader->name);
            $currentPathStr = implode('/', $currentPath);
            if ($path === $currentPathStr) {
                $isCompleted = true;
                $readResult = false;
            } elseif (mb_strpos($path, $currentPathStr) !== 0) {
                array_pop($currentPath);
                $readResult = $reader->next();
            } else {
                $readResult = $reader->read();
            }
        }

        return $isCompleted;
    }

    /**
     * Пересоздает объект для чтения xml.
     *
     * @throws XmlException
     */
    private function resetReader(): \XMLReader
    {
        if (!$this->file || !$this->xpath) {
            throw XmlException::create("File doesn't open");
        }

        $this->unsetReader();
        $this->reader = new \XMLReader();

        $res = $this->reader->open(
            $this->file->getPathname(),
            self::XML_READER_CHARSET,
            self::XML_READER_PARAMS
        );
        if ($res === false) {
            throw XmlException::create("Can't open file '%s' for reading", $this->file->getPathname());
        }

        return $this->reader;
    }

    /**
     * Закрывает открытые ресурсы и сбрасывает все внутренние счетчики.
     */
    private function unsetReader(): void
    {
        if ($this->reader) {
            $this->reader->close();
            $this->reader = null;
        }
    }
}
