<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

/**
 * Класс, который содержит функции, возвращающие пути до различных частей библиотеки.
 */
class PathHelper
{
    /**
     * Возвращает полный путь к папке с ресурсами.
     */
    public static function resources(): string
    {
        return realpath(__DIR__ . '/../../resources');
    }

    /**
     * Возвращает полный путь до файла внутри папки с ресурсами.
     */
    public static function resource(string $resourceName): string
    {
        $path = self::resources();

        return "{$path}/{$resourceName}";
    }
}
