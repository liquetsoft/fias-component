<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

use Liquetsoft\Fias\Component\Exception\FiasFileSelectorException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityBinder;
use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Marvin255\FileSystemHelper\FileSystemHelper;

/**
 * Объект, который выбирает файлы для обработки из указанного источника.
 */
final class FiasFileSelectorImpl implements FiasFileSelector
{
    public function __construct(
        private readonly FiasEntityBinder $binder,
        private readonly Unpacker $unpacker,
        private readonly FileSystemHelper $fs,
        private readonly ?Filter $filter = null
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function select(\SplFileInfo $source): array
    {
        try {
            $files = $this->parseSplFileInfoFiles($source);
            $files = $this->filterFiles($files);
        } catch (\Throwable $e) {
            throw FiasFileSelectorException::wrap($e);
        }

        return $files;
    }

    /**
     * Преобразовывает SplFileInfo в массив файлов для чтения.
     *
     * Пробует прочитать все файлы, если предаставлена папка.
     * Пробует вернуть список файлов, если предоставлен архив, не распаковывая его.
     * Оборачивает обычный файл во внутреннее представление.
     *
     * @return FiasFileSelectorFile[]
     */
    private function parseSplFileInfoFiles(\SplFileInfo $fileInfo): array
    {
        if ($fileInfo->isDir()) {
            return $this->parseDirFiles($fileInfo);
        } elseif ($fileInfo->isFile() && $this->unpacker->isArchive($fileInfo)) {
            return $this->parseArchiveFiles($fileInfo);
        } elseif ($fileInfo->isFile()) {
            return $this->parseFile($fileInfo);
        }

        throw FiasFileSelectorException::create(
            "Source for parsing files '%s' doesn't exist or isn't readable",
            $fileInfo->getPathname()
        );
    }

    /**
     * Выбирает только те файлы, которые имеют отношение к ФИАС и проходят установленный фильтр.
     *
     * @param FiasFileSelectorFile[] $files
     *
     * @return FiasFileSelectorFile[]
     */
    private function filterFiles(array $files): array
    {
        $entites = $this->binder->getBoundEntities();

        $result = [];
        foreach ($files as $file) {
            if ($this->filter && !$this->filter->test($file)) {
                continue;
            }
            foreach ($entites as $entity) {
                if (
                    $entity->isFileNameFitsXmlInsertFileMask($file->getPath())
                    || $entity->isFileNameFitsXmlDeleteFileMask($file->getPath())
                ) {
                    $result[] = $file;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Получает список файлов из папки.
     *
     * @return FiasFileSelectorFile[]
     */
    private function parseDirFiles(\SplFileInfo $dir): array
    {
        $iterator = $this->fs->createDirectoryIterator($dir);

        $result = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $this->unpacker->isArchive($file)) {
                $result += $this->parseArchiveFiles($file);
            } elseif ($file->isFile()) {
                $result += $this->parseFile($file);
            }
        }

        return $result;
    }

    /**
     * Получает список файлов из архива.
     *
     * @return FiasFileSelectorFile[]
     */
    private function parseArchiveFiles(\SplFileInfo $archive): array
    {
        $result = [];
        foreach ($this->unpacker->getListOfFiles($archive) as $archivedFile) {
            $result[] = FiasFileSelectorFileFactory::createFromArchive($archive, $archivedFile);
        }

        return $result;
    }

    /**
     * Преобразует файл во внутреннее представление.
     *
     * @return FiasFileSelectorFile[]
     */
    private function parseFile(\SplFileInfo $file): array
    {
        return [
            FiasFileSelectorFileFactory::createFromFile($file),
        ];
    }
}
