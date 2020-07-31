<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parser;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Reader\Reader;
use SplFileInfo;
use XBase\Record;

/**
 * Описание сущности парсинга файлов dbf.
 */
class DbfParser implements Parser
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        if ($reader->getType() !== 'dbf') {
            throw new InvalidArgumentException('Unexpected reader type');
        }
        $this->reader = $reader;
    }

    /**
     * @inheritdoc
     */
    public function getEntities(SplFileInfo $file, EntityDescriptor $descriptor, string $entityСlass): \Generator
    {
        $this->reader->open($file, $descriptor);

        /** @var Record $record */
        foreach ($this->reader as $record) {
            $result = new $entityСlass;
            foreach ($record->getData() as $field => $value) {
                $result->{$field} = $value;
            }
            yield $result;
        }
        $this->reader->close();
    }
}
