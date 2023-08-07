<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;
use Liquetsoft\Fias\Component\Helper\PathHelper;

/**
 * Объект, который получает описания сущностей ФИАС из php файла с массивом.
 */
final class FiasEntityRepositoryPhpFile extends FiasEntityRepositoryAbstract
{
    private readonly string $pathToSource;

    public function __construct(string $pathToSource = null)
    {
        $this->pathToSource = $pathToSource ?: PathHelper::resource('fias_entities.php');
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress UnresolvableInclude
     */
    protected function loadRepositoryData(): iterable
    {
        try {
            $fileData = include $this->checkAndReturnPath();
        } catch (\Throwable $e) {
            throw FiasEntityException::wrap($e);
        }

        if (!\is_array($fileData)) {
            throw FiasEntityException::create('Php file must return an array');
        }

        $entities = [];
        foreach ($fileData as $key => $entity) {
            if (!\is_array($entity)) {
                throw FiasEntityException::create('All items in the php array must also be arrays');
            }
            $entity['name'] = $key;
            $entities[] = FiasEntityFactory::createFromArray($entity);
        }

        return $entities;
    }

    /**
     * Проверяет, что путь до файла с описанием сущностей существует и возвращает его.
     */
    private function checkAndReturnPath(): string
    {
        if (!file_exists($this->pathToSource)) {
            throw FiasEntityException::create(
                "File '%s' for php entity registry must exists and be readable",
                $this->pathToSource
            );
        }

        $extension = pathinfo($this->pathToSource, \PATHINFO_EXTENSION);
        if ($extension !== 'php') {
            throw FiasEntityException::create(
                "File '%s' must has 'php' extension, got '%s'",
                $this->pathToSource,
                $extension
            );
        }

        return $this->pathToSource;
    }
}
