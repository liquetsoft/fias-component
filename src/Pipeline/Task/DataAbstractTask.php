<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityBinder;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityRepository;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Helper\ArrayHelper;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Liquetsoft\Fias\Component\Storage\Storage;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderProvider;
use Marvin255\FileSystemHelper\FileSystemHelper;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Абстрактный класс для задач связанных с обработкой данных: создания, обновления, удаления.
 *
 * @internal
 */
abstract class DataAbstractTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(
        private readonly FiasEntityRepository $enityRepository,
        private readonly FiasEntityBinder $entityBinder,
        private readonly XmlReaderProvider $xmlReaderProvider,
        private readonly SerializerInterface $serializer,
        private readonly Storage $storage,
        private readonly FileSystemHelper $fs,
        private readonly ?Filter $filter = null
    ) {
    }

    /**
     * Пробует найти дескриптор для указанного файла.
     */
    abstract protected function getFiasEntityByFile(\SplFileInfo $file, FiasEntityRepository $enityRepository): ?FiasEntity;

    /**
     * Обрабатывает одиночную запись из файла.
     */
    abstract protected function processItem(object $item, Storage $storage): void;

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $files = ArrayHelper::ensureArrayElements(
            $state->get(PipelineStateParam::FILES_TO_PROCEED),
            FiasFileSelectorFile::class
        );

        foreach ($files as $file) {
            $this->processFile($file);
        }

        return $state;
    }

    /**
     * Обратывает указанный файл.
     */
    private function processFile(FiasFileSelectorFile $file): void
    {
        if ($file->isArchived()) {
            return;
        }

        $fileInfo = $this->fs->makeFileInfo($file->getPath());

        $entity = $this->getFiasEntityByFile($fileInfo, $this->enityRepository);
        if ($entity === null) {
            return;
        }

        $boundClass = $this->entityBinder->getImplementationByEntityName($entity);
        if ($boundClass === null) {
            return;
        }

        $this->processXml($fileInfo, $entity, $boundClass);
    }

    /**
     * Обрабатывает xml из файла.
     *
     * @psalm-param class-string $boundClass
     */
    private function processXml(\SplFileInfo $file, FiasEntity $entity, string $boundClass): void
    {
        $this->logInfo(
            'Processing data from file',
            [
                'file' => $file->getPath(),
                'entity' => $entity->getName(),
                'xmlPath' => $entity->getXmlPath(),
                'boundClass' => $boundClass,
            ]
        );

        $total = 0;
        $xml = $this->xmlReaderProvider->open($file, $entity->getXmlPath());
        $this->storage->start();
        try {
            foreach ($xml as $itemString) {
                $item = $this->deserializeXmlStringToObject($itemString, $boundClass);
                if (
                    (!$this->filter || $this->filter->test($item))
                    && $this->storage->supports($item)
                ) {
                    $this->processItem($item, $this->storage);
                    ++$total;
                }
            }
        } finally {
            $this->storage->stop();
            $xml->close();
        }

        $this->logInfo(
            'Data processing is completed',
            [
                'total' => $total,
                'file' => $file->getPath(),
                'entity' => $entity->getName(),
                'xmlPath' => $entity->getXmlPath(),
                'boundClass' => $boundClass,
            ]
        );
    }

    /**
     * Преобразует xml строку в объект указанного класса.
     *
     * @psalm-param class-string $boundClass
     */
    private function deserializeXmlStringToObject(?string $xml, string $boundClass): object
    {
        try {
            $object = $this->serializer->deserialize($xml, $boundClass, 'xml');
        } catch (\Throwable $e) {
            throw TaskException::wrap($e);
        }

        return $object;
    }
}
