<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Helper;

use Liquetsoft\Fias\Component\Helper\PathHelper;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для класса, который возвращает пути до частей библиотеки.
 *
 * @internal
 */
final class PathHelperTest extends BaseCase
{
    /**
     * Тест, который проверяет, что класс вернет правильный путь до папки с ресурсами.
     */
    public function testResources(): void
    {
        $realPath = realpath(__DIR__ . '/../../../resources');

        $testPath = PathHelper::resources();

        $this->assertSame($realPath, $testPath);
    }

    /**
     * Тест, который проверяет, что класс вернет правильный путь до ресурса.
     */
    public function testResource(): void
    {
        $fileName = 'test.test';
        $resources = (string) realpath(__DIR__ . '/../../../resources');
        $realPath = "{$resources}/{$fileName}";

        $testPath = PathHelper::resource($fileName);

        $this->assertSame($realPath, $testPath);
    }
}
