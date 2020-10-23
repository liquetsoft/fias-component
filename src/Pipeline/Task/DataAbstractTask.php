<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\Exception\ParserException;
use Liquetsoft\Fias\Component\Exception\StorageException;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Parser\Parser;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Storage\Storage;
use Psr\Log\LogLevel;
use SplFileInfo;

/**
 * Абстрактная задача, которая переносит данные из xml в хранилище данных.
 */
abstract class DataAbstractTask implements Task, LoggableTask
{
    use LoggableTaskTrait;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @param EntityManager $entityManager
     * @param Parser        $parser
     * @param Storage       $storage
     */
    public function __construct(EntityManager $entityManager, Parser $parser, Storage $storage)
    {
        $this->entityManager = $entityManager;
        $this->parser = $parser;
        $this->storage = $storage;
    }

    /**
     * Получает список дескрипторов для файлов, которые нужно обработать.
     *
     * @param State $state
     *
     * @return string[]
     */
    abstract protected function getFileNamesFromState(State $state): array;

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
        $fileNames = $this->getFileNamesFromState($state);
        foreach ($fileNames as $fileName) {
            $this->processFile(new SplFileInfo($fileName));
        }
    }

    /**
     * Обрабатывает указанный файл.
     *
     * @param SplFileInfo $fileInfo
     *
     * @throws TaskException
     * @throws StorageException
     */
    protected function processFile(SplFileInfo $fileInfo): void
    {
        $descriptor = $this->getDescriptorForFile($fileInfo);
        if ($descriptor) {
            $entityClass = $this->entityManager->getClassByDescriptor($descriptor);
            if ($entityClass) {
                $this->processDataFromFile($fileInfo, $descriptor, $entityClass);
                gc_collect_cycles();
            }
        }
    }

    /**
     * Обрабатывает данные из файла и передает в хранилище.
     *
     * @param SplFileInfo      $fileInfo
     * @param EntityDescriptor $descriptor
     * @param string           $entityClass
     *
     * @throws TaskException
     * @throws StorageException
     */
    protected function processDataFromFile(SplFileInfo $fileInfo, EntityDescriptor $descriptor, string $entityClass): void
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
        $this->storage->start();
        try {
            $items = $this->parser->getEntities($fileInfo, $descriptor, $entityClass);
            foreach ($items as $item) {
                if (!$this->storage->supports($item)) {
                    continue;
                }
                $this->processItem($item);
                unset($item);
                ++$total;
            }
        } catch (ParserException $e) {
            throw new TaskException("Error occured during entities parsing: {$e->getMessage()}", 0, $e);
        } finally {
            $this->storage->stop();
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
}
