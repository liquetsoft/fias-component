<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parser;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Reader\Reader;
use Symfony\Component\Serializer\SerializerInterface;
use Liquetsoft\Fias\Component\Exception\ParserException;
use Liquetsoft\Fias\Component\Exception\TaskException;
use InvalidArgumentException;
use SplFileInfo;

/**
 * Описание сущности парсинга файлов xml
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
    public function __construct(Reader $reader, SerializerInterface $serializer = null)
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
    public function getEntities(SplFileInfo $file, EntityDescriptor $descriptor, string $entity_class): \Generator
    {
        $this->reader->open($file, $descriptor);

        foreach ($this->reader as $xml) {
            yield $this->deserializeXmlStringToObject($xml, $entity_class);
        }
        $this->reader->close();
    }

    /**
     * Десериализует xml строку в объект указанного класса.
     *
     * @param string $xml
     * @param string $entity_class
     *
     * @return object
     *
     * @throws ParserException
     */
    protected function deserializeXmlStringToObject(string $xml, string $entity_class): object
    {
        try {
            $entity = $this->serializer->deserialize($xml, $entity_class, 'xml');
        } catch (\Throwable $e) {
            $message = "Deserialization error while deserialization of '{$xml}' string to object with '{$entity_class}' class.";
            throw new TaskException($message, 0, $e);
        }

        if (!is_object($entity)) {
            throw new ParserException('Serializer must returns an object instance.');
        }

        return $entity;
    }
}
