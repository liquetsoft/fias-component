<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponseBase;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который представляет результат со ссылкой на файлы
 * от сервиса ФИАС.
 */
class InformerResponseBaseTest extends BaseCase
{
    /**
     * Проверяет ацессоры для версии.
     */
    public function testGetSetVersion()
    {
        $version = $this->createFakeData()->numberBetween(1, 10000);

        $res = new InformerResponseBase;
        $res->setVersion($version);

        $this->assertSame($version, $res->getVersion());
    }

    /**
     * Проверяет ацессоры для ссылки.
     */
    public function testGetSetUrl()
    {
        $url = $this->createFakeData()->url;

        $res = new InformerResponseBase;
        $res->setUrl($url);

        $this->assertSame($url, $res->getUrl());
    }

    /**
     * Проверяет метод, который возвращает наличие результата в объекте.
     */
    public function testHasResult()
    {
        $res = new InformerResponseBase;

        $this->assertFalse($res->hasResult());

        $res->setVersion($this->createFakeData()->numberBetween(1, 10000));
        $res->setUrl($this->createFakeData()->url);

        $this->assertTrue($res->hasResult());
    }

    /**
     * Проверяет, чтобы сеттер для url выбрасывал исключение при попытке
     * ввести не url.
     */
    public function testSetUrlWrongFormatException()
    {
        $res = new InformerResponseBase;

        $this->expectException(InvalidArgumentException::class);
        $res->setUrl($this->createFakeData()->word);
    }
}
