<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\StorageException;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Exception\XmlException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\XmlReader\XmlReader;
use Psr\Log\LogLevel;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

/**
 * Абстрактная задача, которая переносит данные из xml в хранилище данных.
 */
abstract class DataAbstractTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    protected EntityManager $entityManager;

    protected XmlReader $xmlReader;

    protected Storage $storage;

    protected SerializerInterface $serializer;

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
     * Пробует найти дескриптор для указанного файла.
     *
     * @param SplFileInfo $file
     *
     * @return EntityDescriptor|null
     */
    abstract protected function getFileDescriptor(SplFileInfo $file): ?EntityDescriptor;

    /**
     * Обрабатывает одиночную запись из файла.
     *
     * @param object $item
     */
    abstract protected function processItem(object $item): void;

    /**
     * {@inheritdoc}
     */
    public function run(State $state): void
    {
        $allFiles = $state->getParameter(Task::FILES_TO_PROCEED);
        $allFiles = \is_array($allFiles) ? $allFiles : [];

        foreach ($allFiles as $file) {
            $fileInfo = new SplFileInfo((string) $file);
            if ($descriptor = $this->getFileDescriptor($fileInfo)) {
                $this->processFile($fileInfo, $descriptor);
            }
        }
    }

    /**
     * Обрабатывает указанный файл.
     *
     * @param SplFileInfo      $fileInfo
     * @param EntityDescriptor $descriptor
     *
     * @throws TaskException
     * @throws StorageException
     * @throws XmlException
     */
    protected function processFile(SplFileInfo $fileInfo, EntityDescriptor $descriptor): void
    {
        $entityClass = $this->entityManager->getClassByDescriptor($descriptor);
        if ($entityClass) {
            $this->processDataFromFile($fileInfo, $descriptor->getXmlPath(), $entityClass);
            gc_collect_cycles();
        }
    }

    /**
     * Обрабатывает данные из файла и передает в хранилище.
     *
     * @param SplFileInfo $fileInfo
     * @param string      $xpath
     * @param string      $entityClass
     *
     * @throws TaskException
     * @throws StorageException
     * @throws XmlException
     */
    protected function processDataFromFile(SplFileInfo $fileInfo, string $xpath, string $entityClass): void
    {
        $this->log(
            LogLevel::INFO,
            "Start processing '{$fileInfo->getRealPath()}' file for '{$entityClass}' entity.",
            [
                'entity' => $entityClass,
                'path' => $fileInfo->getRealPath(),
            ]
        );

        $total = 0;
        $this->xmlReader->open($fileInfo, $xpath);
        $this->storage->start();
        try {
            foreach ($this->xmlReader as $xml) {
                $item = $this->deserializeXmlStringToObject($xml, $entityClass);
                if (!$this->storage->supports($item)) {
                    continue;
                }
                $this->processItem($item);
                unset($item, $xml);
                ++$total;
            }
        } finally {
            $this->storage->stop();
            $this->xmlReader->close();
        }

        $this->log(
            LogLevel::INFO,
            "Complete processing '{$fileInfo->getRealPath()}' file for '{$entityClass}' entity. {$total} items processed.",
            [
                'entity' => $entityClass,
                'path' => $fileInfo->getRealPath(),
            ]
        );
    }

    /**
     * Преобразует xml строку в объект указанного класса.
     *
     * @param string|null $xml
     * @param string      $entityClass
     *
     * @return object
     *
     * @throws TaskException
     */
    protected function deserializeXmlStringToObject(?string $xml, string $entityClass): object
    {
        try {
            $entity = $this->serializer->deserialize($xml, $entityClass, 'xml');
        } catch (Throwable $e) {
            $message = "Deserialization error while deserialization of '{$xml}' string to object with '{$entityClass}' class.";
            throw new TaskException($message, 0, $e);
        }

        if (!\is_object($entity)) {
            throw new TaskException('Serializer must returns an object instance.');
        }

        return $entity;
    }
}
