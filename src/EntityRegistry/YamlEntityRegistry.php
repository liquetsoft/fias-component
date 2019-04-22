<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
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
     * @var array|null
     */
    protected $registry;

    /**
     * @param string $pathToYaml Путь к файлу с описанием сущностей
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $pathToYaml)
    {
        $this->pathToYaml = trim($pathToYaml);

        if (!file_exists($this->pathToYaml)) {
            throw new InvalidArgumentException(
                "File '{$this->pathToYaml}' for yaml entity registry doesn't exist."
            );
        }

        if (!is_readable($this->pathToYaml)) {
            throw new InvalidArgumentException(
                "File '{$this->pathToYaml}' for yaml entity registry isn't readable."
            );
        }
    }

    /**
     * Возвращает сущность по псевдониму, если такая существует.
     */

    /**
     * Возвращает массив с описаниями сущностей из указанного yaml файла.
     *
     * @return array
     *
     * @throws ParseException
     */
    protected function getRegistry(): array
    {
        if ($this->registry === null) {
            $this->registry = Yaml::parse($this->pathToYaml);
        }

        return $this->registry;
    }
}
