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
        $version = 123;
        $fullUrl = 'https://test.test/full';
        $deltaUrl = 'https://test.test/delta';

        $res = new BaseInformerResponse($version, $fullUrl, $deltaUrl);

        $this->assertSame($version, $res->getVersion());
    }

    /**
     * Проверяет геттер для ссылки на полную версию.
     */
    public function testGetFullUrl(): void
    {
        $version = 123;
        $fullUrl = 'https://test.test/full';
        $deltaUrl = 'https://test.test/delta';

        $res = new BaseInformerResponse($version, $fullUrl, $deltaUrl);

        $this->assertSame($fullUrl, $res->getFullUrl());
    }

    /**
     * Проверяет геттер для ссылки на изменения в версии.
     */
    public function testGetDeltaUrl(): void
    {
        $version = 123;
        $fullUrl = 'https://test.test/full';
        $deltaUrl = 'https://test.test/delta';

        $res = new BaseInformerResponse($version, $fullUrl, $deltaUrl);

        $this->assertSame($deltaUrl, $res->getDeltaUrl());
    }
}
