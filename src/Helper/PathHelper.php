<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

/**
 * Хэлпер, который содержит пути до различных частей библиотеки.
 */
class PathHelper
{
    /**
     * Возвращает полный путь до файла внутри папки с ресурсами.
     *
     * @param string $resourceName
     *
     * @return string
     */
    public static function resource(string $resourceName): string
    {
        $path = realpath(__DIR__ . '/../../resources');

        return "{$path}/{$resourceName}";
    }
}
