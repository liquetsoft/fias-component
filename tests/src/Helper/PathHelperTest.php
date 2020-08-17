<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Helper;

use Liquetsoft\Fias\Component\Helper\PathHelper;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для хэлпера, который возвращает пути до частей библиотеки.
 */
class PathHelperTest extends BaseCase
{
    /**
     * Тест, который проверяет, что хэлпер вернет правильный путь до папки с ресурсами.
     */
    public function testResources()
    {
        $realPath = realpath(__DIR__ . '/../../../resources');

        $testPath = PathHelper::resources();

        $this->assertSame($realPath, $testPath);
    }

    /**
     * Тест, который проверяет, что хэлпер вернет правильный путь до ресурса.
     */
    public function testResource()
    {
        $fileName = 'test.test';
        $realPath = realpath(__DIR__ . '/../../../resources') . '/' . $fileName;

        $testPath = PathHelper::resource($fileName);

        $this->assertSame($realPath, $testPath);
    }
}
