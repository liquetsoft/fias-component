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
    private const XML_READER_OPTIONS = \LIBXML_COMPACT | \LIBXML_NONET | \LIBXML_NOBLANKS;

    /**
     * Файл, данные из которого нужно получить.
     */
    private ?\SplFileInfo $file = null;

    /**
     * Путь до списка элементов в формате xpath.
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
     * Массив с буфером, для isValid и current.
     */
    private ?string $buffer = null;

    /**
     * {@inheritdoc}
     */
    public function open(\SplFileInfo $file, string $xpath): void
    {
        $this->file = $file;
        $this->xpath = $this->checkAndPrepareXpath($xpath);
        $this->unsetReader();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->file = null;
        $this->xpath = '';
        $this->unsetReader();
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function rewind(): void
    {
        $this->resetReader();
        $this->position = 0;
        $this->buffer = $this->getLine();
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function current(): string
    {
        return $this->buffer ?: '';
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
        $this->buffer = $this->getLine();
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function valid(): bool
    {
        return $this->buffer !== null;
    }

    /**
     * Создает новый объект для чтения xml и устанавливает указатель в начало списка.
     */
    private function resetReader(): void
    {
        $this->unsetReader();
        $this->setReader();
        $this->seekXmlRoot();
    }

    /**
     * Закрывает текущий XmlReader и убирает ссылку на него.
     */
    private function unsetReader(): void
    {
        if ($this->reader !== null) {
            $this->reader->close();
            $this->reader = null;
        }
    }

    /**
     * Создает объект для чтения xml из указанного файла.
     *
     * @psalm-suppress InvalidPropertyAssignmentValue
     */
    private function setReader(): void
    {
        if ($this->file === null) {
            throw XmlException::create("File wasn't opened");
        }

        try {
            $this->reader = \XMLReader::open(
                'file://' . $this->file->getPathname(),
                self::XML_READER_CHARSET,
                self::XML_READER_OPTIONS
            );
        } catch (\Throwable $e) {
            throw XmlException::create(
                "Can't open file '%s' for reading: %s",
                $this->file->getPathname(),
                $e->getMessage()
            );
        }
    }

    /**
     * Возвращает объект для чтения xml файла или пытается создать новый.
     */
    private function getReader(): \XMLReader
    {
        if ($this->reader === null) {
            throw XmlException::create("Iterator wasn't initialized");
        }

        return $this->reader;
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
     */
    private function seekXmlRoot(): void
    {
        $reader = $this->getReader();
        $path = trim($this->xpath, '/');
        $currentPath = [];
        try {
            $readResult = $reader->read();
            while ($readResult) {
                array_push($currentPath, $reader->name);
                $currentPathStr = implode('/', $currentPath);
                if ($path === $currentPathStr) {
                    $readResult = false;
                } elseif (strpos($path, $currentPathStr) !== 0) {
                    array_pop($currentPath);
                    $readResult = $reader->next();
                } else {
                    $readResult = $reader->read();
                }
            }
        } catch (\Throwable $e) {
            throw XmlException::create(
                "Reading error in '%s' file: %s",
                $this->file?->getPathname(),
                $e->getMessage()
            );
        }
    }

    /**
     * Возвращает строку из файла, соответствующую элементу, или null, если разбор
     * файла завершен.
     *
     * @throws XmlException
     */
    private function getLine(): ?string
    {
        $return = null;

        $reader = $this->getReader();
        $arPath = explode('/', $this->xpath);
        $nameFilter = array_pop($arPath);
        $currentDepth = $reader->depth;
        try {
            $this->skipUselessXml($nameFilter, $currentDepth);
            // мы можем выйти из цикла, если найдем нужный элемент
            // или попадем на уровень выше - проверяем, что нашли нужный
            if ($nameFilter === $reader->name) {
                $return = $reader->readOuterXml();
                // нужно передвинуть указатель, чтобы дважды не прочитать
                // один и тот же элемент
                $reader->next();
            }
        } catch (\Throwable $e) {
            throw XmlException::create(
                "Reading error in '%s' file: %s",
                $this->file?->getPathname(),
                $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * Пропускает все xml элементы в текущем ридере, у которых имя или вложенность
     * не совпадают с указанным параметром.
     */
    private function skipUselessXml(string $nodeName, int $nodeDepth): void
    {
        $reader = $this->getReader();
        while (
            $reader->depth === $nodeDepth
            && $nodeName !== $reader->name
            && $reader->next()
        ) {
            continue;
        }
    }

    /**
     * Проверяет и приводит к общему виду строку с xpath.
     */
    private function checkAndPrepareXpath(string $rawXpath): string
    {
        $xpath = trim($rawXpath);

        if (!str_starts_with($xpath, '/') || \strlen($xpath) <= 1) {
            throw XmlException::create("Xpath parameter can't be empty and must start with '/'");
        }

        return $xpath;
    }
}
