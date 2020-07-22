<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parser;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\ParserException;
use Liquetsoft\Fias\Component\Reader\Reader;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Описание сущности парсинга файлов xml.
 */
class XmlParser implements Parser
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
     * @param SerializerInterface $serializer
     */
    public function __construct(Reader $reader, SerializerInterface $serializer)
    {
        if ($reader->getType() !== 'xml') {
            throw new InvalidArgumentException('Unexpected reader type');
        }
        $this->reader = $reader;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function getEntities(SplFileInfo $file, EntityDescriptor $descriptor, string $entityСlass): \Generator
    {
        $this->reader->open($file, $descriptor);

        foreach ($this->reader as $item) {
            yield $this->deserializeXmlStringToObject($item, $entityСlass);
        }
        $this->reader->close();
    }

    /**
     * Десериализует xml строку в объект указанного класса.
     *
     * @param string $xml
     * @param string $entityClass
     *
     * @return object
     *
     * @throws ParserException
     */
    protected function deserializeXmlStringToObject(string $item, string $entityСlass): object
    {
        try {
            $entity = $this->serializer->deserialize($item, $entityСlass, 'xml');
        } catch (\Throwable $e) {
            $message = "Deserialization error while deserialization of '{$item}' string to object with '{$entityСlass}' class.";
            throw new ParserException($message, 0, $e);
        }

        if (!is_object($entity)) {
            throw new ParserException('Serializer must return an object instance.');
        }

        return $entity;
    }
}
