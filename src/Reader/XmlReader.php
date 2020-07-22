<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Reader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\ReaderException;
use RuntimeException;
use SplFileInfo;
use Throwable;
use XmlReader as PhpXmlReader;

/**
 * Объект, который читает данные из файла xml.
 */
class XmlReader implements Reader
{
    /**
     * Файл, который открыт в данный момент.
     *
     * @var SplFileInfo|null
     */
    protected $file;

    /**
     * Xpath, по которому следует искать данные.
     *
     * @var string
     */
    protected $xpath = '';

    /**
     * Объект XMLReader для чтения документа.
     *
     * @var PhpXmlReader|null
     */
    protected $reader;

    /**
     * Текущее смещение внутри массива.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Флаг, который указывает, что данные были прочитаны в буфер.
     *
     * @var bool
     */
    protected $isBufferFull = false;

    /**
     * Массив с буфером, для isValid и current.
     *
     * @var string|null
     */
    protected $buffer;

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'xml';
    }

    /**
     * @inheritdoc
     */
    public function open(SplFileInfo $file, EntityDescriptor $entity_descriptor): bool
    {
        if (!$file->isFile() || !$file->isReadable()) {
            throw new InvalidArgumentException(
                "File '" . $file->getPathname() . "' isn't readable or doesn't exist"
            );
        }

        $this->file = $file;
        $this->xpath = $entity_descriptor->getReaderParams($this->getType());

        return $this->seekXmlPath();
    }

    /**
     * @inheritdoc
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
     * @throws ReaderException
     */
    public function rewind()
    {
        $this->position = 0;
        $this->buffer = null;
        $this->isBufferFull = false;
        $this->seekXmlPath();
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|null
     *
     * @throws ReaderException
     */
    public function current()
    {
        if (!$this->isBufferFull) {
            $this->isBufferFull = true;
            $this->buffer = $this->getLine();
        }

        return $this->buffer;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderException
     */
    public function next()
    {
        ++$this->position;
        $this->isBufferFull = true;
        $this->buffer = $this->getLine();
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderException
     */
    public function valid()
    {
        if (!$this->isBufferFull) {
            $this->isBufferFull = true;
            $this->buffer = $this->getLine();
        }

        return $this->buffer !== null;
    }

    /**
     * Деструктор.
     *
     * Закрывает файл, если он все еще открыт.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Возвращает строку из файла, соответствующую элементу, или null, если разбор
     * файла завершен.
     *
     * @return string|null
     *
     * @throws ReaderException
     */
    protected function getLine(): ?string
    {
        if (!$this->reader) {
            throw new ReaderException('Reader must be set before reading');
        }

        $return = null;
        $arPath = explode('/', $this->xpath);
        $nameFilter = array_pop($arPath);
        $currentDepth = $this->reader->depth;

        try {
            $this->skipUselessXml($nameFilter, $currentDepth);
            //мы можем выйти из цикла, если найдем нужный элемент
            //или попадем на уровень выше - проверяем, что нашли нужный
            if ($nameFilter === $this->reader->name) {
                $return = $this->reader->readOuterXml();
                //нужно передвинуть указатель, чтобы дважды не прочитать
                //один и тот же элемент
                $this->reader->next();
            }
        } catch (Throwable $e) {
            $fileName = $this->file ? $this->file->getPathname() : '';
            $message = "Error while parsing xml '{$fileName}' by '{$this->xpath}' path.";
            throw new ReaderException($message, 0, $e);
        }

        return $return;
    }

    /**
     * Пропускает все xml элементы в текущем ридере, у которых имя или вложенность
     * не совпадают с указанным параметром.
     *
     * @param string $nodeName
     * @param int    $nodeDepth
     *
     * @return void
     */
    protected function skipUselessXml(string $nodeName, int $nodeDepth): void
    {
        while ($this->reader
            && $this->reader->depth === $nodeDepth
            && $nodeName !== $this->reader->name
            && $this->reader->next()
        );
    }

    /**
     * Ищет узел заданный в маппере, прежде, чем начать перебор
     * элементов.
     *
     * Если собранный путь лежит в начале строки, которую мы ищем,
     * то продолжаем поиск.
     * Если собранный путь совпадает с тем, что мы ищем,
     * то выходим из цикла.
     * Если путь не совпадает и не лежит в начале строки,
     * то пропускаем данный узел со всеми вложенными деревьями.
     *
     * @return bool
     *
     * @throws ReaderException
     */
    protected function seekXmlPath(): bool
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
     * @return PhpXmlReader
     *
     * @throws ReaderException
     */
    protected function resetReader(): PhpXmlReader
    {
        if (!$this->file || !$this->xpath) {
            throw new ReaderException("File doesn't open.");
        }

        $this->unsetReader();
        $this->reader = new PhpXmlReader;

        if ($this->reader->open(
            $this->file->getPathname(),
            'UTF-8',
            LIBXML_COMPACT | LIBXML_NONET | LIBXML_NOBLANKS
        ) === false) {
            throw new RuntimeException(
                "Can't open file '" . $this->file->getPathname() . "' for reading."
            );
        }

        return $this->reader;
    }

    /**
     * Закрывает открытые ресурсы и ресетит все внутренние счетчики.
     *
     * @return void
     */
    protected function unsetReader()
    {
        if ($this->reader) {
            $this->reader->close();
            $this->reader = null;
        }
    }
}
