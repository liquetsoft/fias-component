<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

/**
 * Класс, который содержит функции, возвращающие пути до различных частей библиотеки.
 */
final class PathHelper
{
    private function __construct()
    {
    }

    /**
     * Возвращает полный путь к папке с ресурсами.
     */
    public static function resources(): string
    {
        $path = realpath(__DIR__ . '/../../resources');

        if ($path === false) {
            throw new \RuntimeException("Can't find resources folder, please check library status");
        }

        return $path;
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
