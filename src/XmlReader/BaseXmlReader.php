<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use Liquetsoft\Fias\Component\Exception\XmlException;
use RuntimeException;
use InvalidArgumentException;
use XmlReader;
use SplFileInfo;

/**
 * Объект, который читает данные из xml файла с помощью XmlReader.
 */
class BaseXmlReader implements \Liquetsoft\Fias\Component\XmlReader\XmlReader
{
    /**
     * Файл, который открыт в данный момент.
     *
     * @var SplFileInfo|null
     */
    protected $file;

    /**
     * Xpath, по которомуследует искать данные.
     *
     * @var string
     */
    protected $xpath = '';

    /**
     * Объект XMLReader для чтения документа.
     *
     * @var XMLReader|null
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
     * Массив с буффером, для isValid и current.
     *
     * @var string|null
     */
    protected $buffer;

    /**
     * @inheritdoc
     */
    public function open(SplFileInfo $file, string $xpath): bool
    {
        if (!$file->isFile() || !$file->isReadable()) {
            throw new InvalidArgumentException(
                "File '" . $file->getPathname() . "' isn't readable or doesn't exist"
            );
        }

        $this->file = $file;
        $this->xpath = $xpath;

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
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
        $this->buffer = null;
        $this->isBufferFull = false;
        $this->seekXmlPath();
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function next()
    {
        ++$this->position;
        $this->isBufferFull = true;
        $this->buffer = $this->getLine();
    }

    /**
     * @inheritdoc
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
     * @throws XmlException
     */
    protected function getLine(): ?string
    {
        if (!$this->reader || !$this->xpath) {
            throw new XmlException('Reader and xpath must be set before reading');
        }

        $return = null;
        $arPath = explode('/', $this->xpath);
        $nameFilter = array_pop($arPath);
        $currentDepth = $this->reader->depth;

        $this->skipUselessXml($nameFilter, $currentDepth);

        //мы можем выйти из цикла, если найдем нужный элемент
        //или попадем на уровень выше - проверяем, что нашли нужный
        if ($nameFilter === $this->reader->name) {
            $return = $this->reader->readOuterXml();
            //нужно передвинуть указатель, чтобы дважды не прочитать
            //один и тот же элемент
            $this->reader->next();
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
        while (
            $this->reader
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
     * @throws XmlException
     */
    protected function seekXmlPath(): bool
    {
        $this->resetReader();

        if (!$this->reader || !$this->xpath) {
            throw new XmlException('Reader and xpath must be set before reading');
        }

        $path = trim($this->xpath, '/');
        $currentPath = [];
        $isCompleted = false;
        $readResult = $this->reader->read();

        while ($readResult) {
            array_push($currentPath, $this->reader->name);
            $currentPathStr = implode('/', $currentPath);
            if ($path === $currentPathStr) {
                $isCompleted = true;
                $readResult = false;
            } elseif (mb_strpos($path, $currentPathStr) !== 0) {
                array_pop($currentPath);
                $readResult = $this->reader->next();
            } else {
                $readResult = $this->reader->read();
            }
        }

        return $isCompleted;
    }

    /**
     * Пересоздает объект для чтения xml.
     *
     * @return void
     *
     * @throws XmlException
     */
    protected function resetReader()
    {
        if (empty($this->file)) {
            throw new XmlException("File doesn't open");
        }

        $this->unsetReader();
        $this->reader = new XmlReader;

        if ($this->reader->open($this->file->getPathname()) === false) {
            throw new RuntimeException(
                "Can't open file '" . $this->file->getPathname() . "' for reading"
            );
        }
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
