<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\XmlReader\XmlReader;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Symfony\Component\Serializer\SerializerInterface;
use SplFileInfo;
use RecursiveDirectoryIterator;

/**
 * Абстрактная задача, которая переносит данные из xml в хранилище данных.
 */
abstract class DataAbstractTask implements Task
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
     * Получает дескриптор по имени файла.
     *
     * @param SplFileInfo $fileInfo
     *
     * @return EntityDescriptor|null
     */
    abstract protected function getDescriptorForFile(SplFileInfo $fileInfo): ?EntityDescriptor;

    /**
     * Обрабатывает одиночную запись из файла.
     *
     * @param object $item
     */
    abstract protected function processItem(object $item): void;

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
     * Обрабатывает указанный файл.
     *
     * @param SplFileInfo $fileInfo
     */
    protected function processFile(SplFileInfo $fileInfo): void
    {
        $descriptor = $this->getDescriptorForFile($fileInfo);
        if ($descriptor) {
            $entityClass = $this->entityManager->getClassByDescriptor($descriptor);
            if ($entityClass) {
                $this->processDataFromFile($fileInfo, $descriptor->getXmlPath(), $entityClass);
            }
        }
    }

    /**
     * Обрабатывает данные из файла и передает в хранилище.
     *
     * @param SplFileInfo $fileInfo
     * @param string      $xpath
     * @param string      $entityClass
     */
    protected function processDataFromFile(SplFileInfo $fileInfo, string $xpath, string $entityClass): void
    {
        $this->xmlReader->open($fileInfo, $xpath);
        $this->storage->start();

        foreach ($this->xmlReader as $xml) {
            $entity = $this->serializer->deserialize($xml, $entityClass, 'xml');
            $this->processItem($entity);
        }

        $this->storage->stop();
        $this->xmlReader->close();
    }
}
