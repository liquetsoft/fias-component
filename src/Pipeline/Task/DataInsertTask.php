<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\XmlReader\XmlReader;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Symfony\Component\Serializer\SerializerInterface;
use SplFileInfo;
use RecursiveDirectoryIterator;

/**
 * Задача, которая читает данные из xml и вставляет их в БД.
 */
class DataInsertTask implements Task
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var XmlReader
     */
    protected $xmlReader;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param EntityManager       $entityManager
     * @param XmlReader           $xmlReader
     * @param Storage             $storage
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManager $entityManager, XmlReader $xmlReader, Storage $storage, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->xmlReader = $xmlReader;
        $this->storage = $storage;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $filesFolder = $state->getParameter('unpackTo');
        if (!($filesFolder instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter 'unpackTo' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $iterator = new RecursiveDirectoryIterator(
            $filesFolder->getRealPath(),
            RecursiveDirectoryIterator::SKIP_DOTS
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $this->processFile($fileInfo);
            }
        }
    }

    /**
     * Обрабатывает файл для вставки данных.
     *
     * @param SplFileInfo $fileInfo
     */
    protected function processFile(SplFileInfo $fileInfo): void
    {
        $descriptor = $this->entityManager->getDescriptorByInsertFile($fileInfo->getFilename());
        if ($descriptor) {
            $entityClass = $this->entityManager->getClassByDescriptor($descriptor);
            if ($entityClass) {
                $this->insertDataFromFile($fileInfo, $descriptor->getXmlPath(), $entityClass);
            }
        }
    }

    /**
     * Читает и загружает данные в БД.
     *
     * @param SplFileInfo $fileInfo
     * @param string      $xpath
     * @param string      $entityClass
     */
    protected function insertDataFromFile(SplFileInfo $fileInfo, string $xpath, string $entityClass): void
    {
        $this->xmlReader->open($fileInfo, $xpath);
        $this->storage->start();

        foreach ($this->xmlReader as $xml) {
            $entity = $this->serializer->deserialize($xml, $entityClass, 'xml');
            $this->storage->insert($entity);
        }

        $this->storage->stop();
        $this->xmlReader->close();
    }
}
