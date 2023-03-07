<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\FiasInformer\BaseInformerResponse;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который представляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 *
 * @internal
 */
class BaseInformerResponseTest extends BaseCase
{
    /**
     * Проверяет геттер для версии.
     */
    public function testGetVersion(): void
    {
        $url = 'https://test.test/test';
        $version = 123;

        $res = new BaseInformerResponse($version, $url);

        $this->assertSame($version, $res->getVersion());
    }

    /**
     * Проверяет геттер для ссылки.
     */
    public function testGetUrl(): void
    {
        $url = 'https://test.test/test';
        $version = 123;

        $res = new BaseInformerResponse($version, $url);

        $this->assertSame($url, $res->getUrl());
    }
}
