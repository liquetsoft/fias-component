<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parser;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Reader\Reader;
use Symfony\Component\Serializer\SerializerInterface;
use InvalidArgumentException;
use SplFileInfo;

use function DeepCopy\deep_copy;

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
    public function getEntities(SplFileInfo $file, EntityDescriptor $descriptor, string $entity_class = null): \Generator
    {
        $this->reader->open($file, $descriptor);

        while ($record = $this->reader->next()) {
            yield deep_copy($record);
        }
        $this->reader->close();
    }
}
