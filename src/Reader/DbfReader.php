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
     * Файл, который открыт в данный момент.
     *
     * @var SplFileInfo|null
     */
    protected $file;

    /**
     * Массив используемых столбцов. При значении null выбираются все столбцы по умолчанию.
     *
     * @var array|null
     */
    protected $dbfColumns;

    /**
     * Исходная кодировка таблицы. При значении null выбирается кодировка UTF-8 по умолчанию.
     *
     * @var string|null
     */
    protected $dbfEncoding;

    /**
     * Таблица с данными.
     *
     * @var Table|null
     */
    protected $table;

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

        $dbfColumns = [];
        foreach ($entity_descriptor->getFields() as $field) {
            $dbfColumns[] = $field->getName();
        }
        
        $this->dbfColumns = $dbfColumns;
        $this->dbfEncoding = $entity_descriptor->getReaderParams($this->getType());

        try {
            $this->table = new Table($file->getPathname(), $this->dbfColumns, $this->dbfEncoding);
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
        $this->file = null;
        $this->dbfColumns = null;
        $this->dbfEncoding = null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderException
     */
    public function rewind()
    {
        try {
            $this->table->moveTo(0);
        } catch (Throwable $e) {
            $message = "Error during rewind position in dbf reader";
            throw new ReaderException($message, 0, $e);
        }
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
        try {
            return $this->table->getRecord();
        } catch (Throwable $e) {
            $message = "Error during returning current item in dbf reader";
            throw new ReaderException($message, 0, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->table->getRecordPos();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        try {
            return $this->table->nextRecord();
        } catch (Throwable $e) {
            $message = "Error during returning next item in dbf reader";
            throw new ReaderException($message, 0, $e);
        }
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
        if ($position >= $this->table->getRecordCount() || $position < 0) {
            throw new ReaderException("Row with index {$position} does not exists");
        }
        return true;
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
     */
    public function getTableColumns()
    {
        if (!$this->table) {
            throw new ReaderException('Reader must be set before reading');
        }
        return $this->table->getColumns();
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
}
