<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Process;

use Symfony\Component\Process\Process;

/**
 * Объект, который создает symfony process по определенному шаблону.
 *
 * Шаблон команды задается строкой, а все аргументы или опции для замены через фигурные скобки:
 * command {{argument_1}} --option {{option_value}}
 */
class TemplateProcess
{
    /**
     * @var string
     */
    protected $template;

    /**
     * @param string $template
     */
    public function __construct(string $template)
    {
        $this->template = $template;
    }

    /**
     * Создает новый процесс из раблона с помощью подстановок.
     *
     * @param array $replaces
     *
     * @return Process
     */
    public function createProcess(array $replaces = []): Process
    {
        $commandArray = $this->hydrateCommandTemplate($this->template, $replaces);

        return new Process($commandArray);
    }

    /**
     * Заменяет подстановки в шаблоне команды.
     *
     * @param string $template
     * @param array  $replaces
     *
     * @return array
     */
    protected function hydrateCommandTemplate(string $template, array $replaces = []): array
    {
        $commandArray = [];

        $splitTemplate = preg_split(
            '/(\{\{[a-zA-Z0-9_\-]+\}\})/',
            $template,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        foreach ($splitTemplate as $commandPart) {
            $commandPart = trim($commandPart);
            if (preg_match('/^\{\{([a-zA-Z0-9_\-]+)\}\}$/', $commandPart, $matches)) {
                $commandArray[] = $replaces[$matches[1]] ?? '';
            } elseif ($commandPart !== '') {
                $commandArray = array_merge($commandArray, explode(' ', $commandPart));
            }
        }

        $commandArray = array_diff($commandArray, ['']);

        return $commandArray;
    }
}
