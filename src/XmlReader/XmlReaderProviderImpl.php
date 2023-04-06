<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use Liquetsoft\Fias\Component\Exception\XmlException;

/**
 * Объект, который создает и возвращает php XMLReader для указанного файла.
 */
final class XmlReaderProviderImpl implements XmlReaderProvider
{
    public const XML_READER_CHARSET = 'UTF-8';
    public const XML_READER_OPTIONS = \LIBXML_COMPACT | \LIBXML_NONET | \LIBXML_NOBLANKS;

    /**
     * {@inheritdoc}
     */
    public function open(\SplFileInfo $file, string $xpath): XmlReaderIterator
    {
        $phpXmlReader = $this->openPhpXmlReader($file);

        return new XmlReaderIteratorImpl($phpXmlReader, $xpath);
    }

    /**
     * Создает и возвращает объект php XMLReader для указанного файла.
     */
    private function openPhpXmlReader(\SplFileInfo $file): \XMLReader
    {
        try {
            /** @var \XMLReader */
            $reader = \XMLReader::open(
                $file->getPathname(),
                self::XML_READER_CHARSET,
                self::XML_READER_OPTIONS
            );
        } catch (\Throwable $e) {
            throw XmlException::create(
                "Can't open file '%s': %s",
                $file->getPathname(),
                $e->getMessage()
            );
        }

        return $reader;
    }
}
