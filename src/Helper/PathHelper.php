<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

/**
 * Хэлпер, который содержит пути до различных частей библиотеки.
 */
class PathHelper
{
    /**
     * Возвращает полный путь к папке с ресурсами.
     *
     * @return string
     */
    public static function resources(): string
    {
        return realpath(__DIR__ . '/../../resources');
    }

    /**
     * Возвращает полный путь до файла внутри папки с ресурсами.
     *
     * @param string $resourceName
     *
     * @return string
     */
    public static function resource(string $resourceName): string
    {
        $path = self::resources();

        return "{$path}/{$resourceName}";
    }
}
