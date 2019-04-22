<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\EntityDescriptor\BaseEntityDescriptor;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityField\BaseEntityField;
use Liquetsoft\Fias\Component\EntityField\EntityField;
use Liquetsoft\Fias\Component\Exception\EntityRegistryException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use InvalidArgumentException;

/**
 * Объект, который получает описания сущностей из yaml файла.
 */
class YamlEntityRegistry implements EntityRegistry
{
    /**
     * @var string
     */
    protected $pathToYaml;

    /**
     * @var string[]
     */
    protected $bindings = [];

    /**
     * @var EntityDescriptor[]|null
     */
    protected $registry;

    /**
     * @param string   $pathToYaml Путь к файлу с описанием сущностей
     * @param string[] $bindings   Соответствия между классами реализации и именами сущностей
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $pathToYaml, array $bindings = [])
    {
        if (!file_exists($pathToYaml) || !is_readable($pathToYaml)) {
            throw new InvalidArgumentException(
                "File '{$pathToYaml}' for yaml entity registry isn't readable or doesn't exist."
            );
        }
        $this->pathToYaml = trim($pathToYaml);

        if (array_unique($bindings) !== $bindings) {
            throw new InvalidArgumentException(
                "There are doubling entity names in bindings: '" . json_encode($bindings) . "'."
            );
        }
        $this->bindings = [];
        foreach ($bindings as $className => $entityName) {
            $this->bindings[$this->normalizeClassName($className)] = $this->normalizeEntityName($entityName);
        }
    }

    /**
     * @inheritdoc
     */
    public function hasEntityDescriptor(string $entityName): bool
    {
        $return = false;
        $normalizedName = $this->normalizeEntityName($entityName);

        foreach ($this->getRegistry() as $descriptor) {
            $normalizedDescriptorName = $this->normalizeEntityName($descriptor->getName());
            if ($normalizedName === $normalizedDescriptorName) {
                $return = true;
                break;
            }
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function getEntityDescriptor(string $entityName): EntityDescriptor
    {
        $return = null;
        $normalizedName = $this->normalizeEntityName($entityName);

        foreach ($this->getRegistry() as $descriptor) {
            $normalizedDescriptorName = $this->normalizeEntityName($descriptor->getName());
            if ($normalizedName === $normalizedDescriptorName) {
                $return = $descriptor;
                break;
            }
        }

        if (!$return) {
            throw new InvalidArgumentException(
                "Can't fin entity with name '{$entityName}' in '{$this->pathToYaml}'."
            );
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function getDescriptorForClass(string $className): EntityDescriptor
    {
        $return = null;
        $normalizedClassName = $this->normalizeClassName($className);

        if (isset($this->bindings[$normalizedClassName])) {
            $return = $this->getEntityDescriptor($this->bindings[$normalizedClassName]);
        }

        if (!$return) {
            throw new InvalidArgumentException(
                "Can't fin entity for class '{$className}' in '{$this->pathToYaml}'."
            );
        }

        return $return;
    }

    /**
     * Возвращает массив с описаниями сущностей из указанного yaml файла.
     *
     * @return EntityDescriptor[]
     *
     * @throws EntityRegistryException
     */
    protected function getRegistry(): array
    {
        if ($this->registry === null) {
            $this->registry = [];
            try {
                $yaml = Yaml::parseFile($this->pathToYaml);
                foreach ($yaml as $key => $entity) {
                    $entity['name'] = $key;
                    $this->registry[] = $this->createEntityDescriptorFromYaml($entity);
                }
            } catch (Throwable $e) {
                $message = "Error while parsing '{$this->pathToYaml}'";
                throw new EntityRegistryException($message, 0, $e);
            }
        }

        return $this->registry;
    }

    /**
     * Создает сущность из массива, который был записан в yaml файле.
     *
     * @param array $entity
     *
     * @return EntityDescriptor
     *
     * @throws InvalidArgumentException
     */
    protected function createEntityDescriptorFromYaml(array $entity): EntityDescriptor
    {
        if (!empty($entity['fields']) && is_array($entity['fields'])) {
            $fields = [];
            foreach ($entity['fields'] as $key => $field) {
                $field['name'] = $key;
                $fields[] = $this->createEntityFieldFromYaml($field);
            }
            $entity['fields'] = $fields;
        }

        return new BaseEntityDescriptor($entity);
    }

    /**
     * Создает поле из массива, который был записан в yaml файле.
     *
     * @param array $field
     *
     * @return EntityField
     *
     * @throws InvalidArgumentException
     */
    protected function createEntityFieldFromYaml(array $field): EntityField
    {
        return new BaseEntityField($field);
    }

    /**
     * Приводит имена сущностей к единообразному виду.
     *
     * @param string $name
     *
     * @return string
     */
    public function normalizeEntityName(string $name): string
    {
        return trim(strtolower($name));
    }

    /**
     * Приводит имена сущностей к единообразному виду.
     *
     * @param string $name
     *
     * @return string
     */
    public function normalizeClassName(string $name): string
    {
        return trim($name, ' \\/');
    }
}
