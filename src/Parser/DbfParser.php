<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parser;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Reader\Reader;
use Symfony\Component\Serializer\SerializerInterface;
use InvalidArgumentException;
use SplFileInfo;

/**
 * Описание сущности парсинга файлов dbf
 */
class DbfParser implements Parser
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param Reader              $reader
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

        /** @var XBase\Record\Record $record */
        while ($record = $this->reader->next()) {
            $result = new $entityСlass;
            foreach ($record->getData() as $field => $value) {
                $result->{$field} = $value;
            }
            yield $result;
        }
        $this->reader->close();
    }
}
