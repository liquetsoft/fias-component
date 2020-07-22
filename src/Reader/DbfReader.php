<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Reader;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\Exception\ReaderException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Reader\Reader;
use SplFileInfo;
use Throwable;
use XBase\Table;

/**
 * Объект, который читает данные из файла xml
 */
class DbfReader implements Reader
{
    /**
     * Таблица с данными.
     *
     * @var Table|null
     */
    public $table;

    /**
     * Текущее смещение внутри массива.
     *
     * @var int
     */
    protected $position = 0;

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

        // Массив используемых столбцов. При значении null выбираются все столбцы по умолчанию.
        $dbfColumns = [];
        foreach ($entity_descriptor->getFields() as $field) {
            $dbfColumns[] = $field->getName();
        }
        // Исходная кодировка таблицы. При значении null выбирается кодировка UTF-8 по умолчанию.
        $dbfEncoding = $entity_descriptor->getReaderParams($this->getType());

        try {
            $this->table = new Table($file->getPathname(), $dbfColumns, $dbfEncoding);
        } catch (Throwable $e) {
            $message = "Error during create/open dbf table";
            throw new ReaderException($message, 0, $e);
        }
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function close(): void
    {
        if ($this->table) {
            $this->table->close();
            $this->table = null;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderException
     */
    public function rewind()
    {
        $this->position = 0;
        if (!$this->table) {
            throw new ReaderException('Reader must be set before reading');
        }
        $this->table->moveTo($this->position);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderException
     */
    public function current()
    {
        try {
            if (!$this->table) {
                throw new ReaderException('Reader must be set before reading');
            }
            return $this->table->getRecord();
        } catch (Throwable $e) {
            $message = "Error during returning current item in dbf reader";
            throw new ReaderException($message, 0, $e);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws ReaderException
     */
    public function key()
    {
        if (!$this->table) {
            throw new ReaderException('Reader must be set before reading');
        }
        return $this->table->getRecordPos();
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderException
     */
    public function next()
    {
        if (!$this->table) {
            throw new ReaderException('Reader must be set before reading');
        }
        $this->table->moveTo(++$this->position);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderException
     */
    public function valid()
    {
        if (!$this->table) {
            throw new ReaderException('Reader must be set before reading');
        }
        $position = $this->key();
        return ($position < $this->table->getRecordCount() && $position >= 0);
    }
    
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'dbf';
    }
    
    /**
     * Получить используемую кодировку
     *
     * @return string|null
     *
     * @throws ReaderException
     */
    public function getEncoding()
    {
        if (!$this->table) {
            throw new ReaderException('Reader must be set before reading');
        }
        return $this->table->getConvertFrom();
    }

    /**
     * Получить используемые столбцы
     *
     * @return array|null
     *
     * @throws ReaderException
     */
    public function getTableColumns()
    {
        if (!$this->table) {
            throw new ReaderException('Reader must be set before reading');
        }
        return $this->table->getColumns();
    }

    /**
     * Деструктор, закрывает файл, если он все еще открыт.
     */
    public function __destruct()
    {
        $this->close();
    }
}
