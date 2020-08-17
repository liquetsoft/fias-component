<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\BaseEntityDescriptor;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityField\BaseEntityField;
use Liquetsoft\Fias\Component\EntityField\EntityField;
use Liquetsoft\Fias\Component\Helper\PathHelper;

/**
 * Объект, который получает описания сущностей ФИАС из php файла с массивом.
 */
class PhpArrayFileRegistry extends AbstractEntityRegistry
{
    /**
     * @var string
     */
    private $pathToSource;

    /**
     * @param string $pathToSource Путь к файлу с описанием сущностей
     */
    public function __construct(?string $pathToSource = null)
    {
        if ($pathToSource === null) {
            $pathToSource = PathHelper::resource('fias_entities.php');
        }

        $this->pathToSource = $pathToSource;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress UnresolvableInclude
     */
    protected function createRegistry(): array
    {
        $registry = [];

        $fileData = include $this->checkAndReturnPath();
        $fileData = is_array($fileData) ? $fileData : [];

        foreach ($fileData as $key => $entity) {
            $entity['name'] = $key;
            $registry[] = $this->createEntityDescriptor($entity);
        }

        return $registry;
    }

    /**
     * Создает сущность из массива, который был записан в файле.
     *
     * @param array $entity
     *
     * @return EntityDescriptor
     *
     * @throws InvalidArgumentException
     */
    private function createEntityDescriptor(array $entity): EntityDescriptor
    {
        if (!empty($entity['fields']) && is_array($entity['fields'])) {
            $fields = [];
            foreach ($entity['fields'] as $key => $field) {
                $field['name'] = $key;
                $fields[] = $this->createEntityField($field);
            }
            $entity['fields'] = $fields;
        }

        return new BaseEntityDescriptor($entity);
    }

    /**
     * Создает поле из массива, который был записан в файле.
     *
     * @param array $field
     *
     * @return EntityField
     *
     * @throws InvalidArgumentException
     */
    private function createEntityField(array $field): EntityField
    {
        return new BaseEntityField($field);
    }

    /**
     * Проверяет, что путь до файла с описанием сущностей существует и возвращает его.
     *
     * @return string
     */
    private function checkAndReturnPath(): string
    {
        $path = realpath(trim($this->pathToSource));

        if (empty($path)) {
            $message = sprintf(
                "File '%s' for php entity registry doesn't exist.",
                $this->pathToSource
            );
            throw new InvalidArgumentException($message);
        }

        if (!is_readable($path)) {
            $message = sprintf(
                "File '%s' for php entity registry isn't readable.",
                $this->pathToSource
            );
            throw new InvalidArgumentException($message);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension !== 'php') {
            $message = sprintf(
                "File '%s' must has 'php' extension, got '%s'.",
                $this->pathToSource,
                $extension
            );
            throw new InvalidArgumentException($message);
        }

        return $path;
    }
}
