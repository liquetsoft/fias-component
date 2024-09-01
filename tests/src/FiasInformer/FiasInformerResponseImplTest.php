<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponseImpl;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который представляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 *
 * @internal
 */
class FiasInformerResponseImplTest extends BaseCase
{
    /**
     * Проверяет, что объет выбросит исключение, если указать 0 в качестве версии.
     */
    public function testConstructEmptyVersionException(): void
    {
        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage('Version must be more than zero');
        new FiasInformerResponseImpl(0, 'https://test.test/full', 'https://test.test/full');
    }

    /**
     * Проверяет, что объет выбросит исключение, если указать отрицательное число в качестве версии.
     */
    public function testConstructNegativeVersionException(): void
    {
        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage('Version must be more than zero');
        new FiasInformerResponseImpl(-1, 'https://test.test/full', 'https://test.test/full');
    }

    /**
     * Проверяет, что объет выбросит исключение, если некорректную ссылку на полную версию.
     */
    public function testConstructMalformedFullUrlException(): void
    {
        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage('malformed full url');
        new FiasInformerResponseImpl(1, 'malformed full url', 'https://test.test/full');
    }

    /**
     * Проверяет, что объет выбросит исключение, если некорректную ссылку на дельта версию.
     */
    public function testConstructMalformedDeltaUrlException(): void
    {
        $this->expectException(FiasInformerException::class);
        $this->expectExceptionMessage('malformed delta url');
        new FiasInformerResponseImpl(1, 'https://test.test/full', 'malformed delta url');
    }

    /**
     * Проверяет геттер для версии.
     */
    public function testGetVersion(): void
    {
        $version = 123;
        $fullUrl = 'https://test.test/full';
        $deltaUrl = 'https://test.test/delta';

        $res = new FiasInformerResponseImpl($version, $fullUrl, $deltaUrl);

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

        $res = new FiasInformerResponseImpl($version, $fullUrl, $deltaUrl);

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

        $res = new FiasInformerResponseImpl($version, $fullUrl, $deltaUrl);

        $this->assertSame($deltaUrl, $res->getDeltaUrl());
    }
}
