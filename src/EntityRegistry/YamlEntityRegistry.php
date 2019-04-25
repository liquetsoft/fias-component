<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\EntityDescriptor\BaseEntityDescriptor;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityField\BaseEntityField;
use Liquetsoft\Fias\Component\EntityField\EntityField;
use Symfony\Component\Yaml\Yaml;
use InvalidArgumentException;

/**
 * Объект, который получает описания сущностей ФИАС из yaml файла.
 */
class YamlEntityRegistry extends AbstractEntityRegistry
{
    /**
     * @var string
     */
    protected $pathToYaml;

    /**
     * @param string $pathToYaml Путь к файлу с описанием сущностей
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $pathToYaml)
    {
        if (!file_exists($pathToYaml) || !is_readable($pathToYaml)) {
            throw new InvalidArgumentException(
                "File '{$pathToYaml}' for yaml entity registry isn't readable or doesn't exist."
            );
        }

        $this->pathToYaml = trim($pathToYaml);
    }

    /**
     * @inheritdoc
     */
    protected function createRegistry(): array
    {
        $registry = [];

        $yaml = Yaml::parseFile($this->pathToYaml);
        foreach ($yaml as $key => $entity) {
            $entity['name'] = $key;
            $registry[] = $this->createEntityDescriptorFromYaml($entity);
        }

        return $registry;
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
}
