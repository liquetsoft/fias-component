<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFileFactory;
use Liquetsoft\Fias\Component\Helper\ArrayHelper;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Marvin255\FileSystemHelper\FileSystemHelper;

/**
 * Задача, которая распаковывает файлы из архива.
 */
final class UnpackFilesToProceedTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(
        private readonly Unpacker $unpacker,
        private readonly FileSystemHelper $fs
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $targetFolder = $state->get(PipelineStateParam::EXTRACT_TO_FOLDER);
        $targetFolder = $this->fs->makeFileInfo($targetFolder);

        $files = ArrayHelper::ensureArrayElements(
            $state->get(PipelineStateParam::FILES_TO_PROCEED),
            FiasFileSelectorFile::class
        );

        $unpackedFiles = array_map(
            fn (FiasFileSelectorFile $file): FiasFileSelectorFile => $this->unpackFile($file, $targetFolder),
            $files
        );

        return $state->with(PipelineStateParam::FILES_TO_PROCEED, $unpackedFiles);
    }

    /**
     * Распаковывает файлы, которые нуждаются в распаковке.
     */
    private function unpackFile(FiasFileSelectorFile $file, \SplFileInfo $targetFolder): FiasFileSelectorFile
    {
        if (!$file->isArchived()) {
            return $file;
        }

        $this->logInfo(
            'Unpacking file',
            [
                'archive' => $file->getPathToArchive(),
                'file' => $file->getPath(),
                'target' => $targetFolder->getRealPath(),
            ]
        );

        $archive = $this->fs->makeFileInfo($file->getPathToArchive());
        $pathToExtracted = $this->unpacker->extractEntity($archive, $file->getPath(), $targetFolder);

        return FiasFileSelectorFileFactory::createFromFile(
            $this->fs->makeFileInfo($pathToExtracted)
        );
    }
}
