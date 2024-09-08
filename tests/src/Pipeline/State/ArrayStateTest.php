<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\State;

use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит состояние во внутреннем массиве.
 *
 * @internal
 */
class ArrayStateTest extends BaseCase
{
    /**
     * Проверяет запись и получение параметра.
     */
    public function testSetAndGetParameter(): void
    {
        $parameter = StateParameter::TEST;
        $parameterValue = new \stdClass();

        $state = new ArrayState();
        $state->setParameter($parameter, $parameterValue);
        $res = $state->getParameter($parameter);

        $this->assertSame($parameterValue, $res);
    }

    /**
     * Проверяет, что объект вернет значение по умолчанию, если параметр не указан.
     */
    public function testGetParameterDefault(): void
    {
        $parameter = StateParameter::TEST;
        $defaultValue = 123;

        $state = new ArrayState();
        $res = $state->getParameter($parameter, $defaultValue);

        $this->assertSame($defaultValue, $res);
    }

    /**
     * Проверяет запись и получение int параметра.
     */
    public function testSetAndGetParameterInt(): void
    {
        $parameter = StateParameter::TEST;
        $parameterValue = 123;

        $state = new ArrayState();
        $state->setParameter($parameter, $parameterValue);
        $res = $state->getParameterInt($parameter);

        $this->assertSame($parameterValue, $res);
    }

    /**
     * Проверяет запись и получение string параметра.
     */
    public function testSetAndGetParameterString(): void
    {
        $parameter = StateParameter::TEST;
        $parameterValue = 'string';

        $state = new ArrayState();
        $state->setParameter($parameter, $parameterValue);
        $res = $state->getParameterString($parameter);

        $this->assertSame($parameterValue, $res);
    }

    /**
     * Проверяет, что объект правильно задает константы.
     */
    public function testSetAndLockParameter(): void
    {
        $parameter = StateParameter::TEST;
        $parameterValue = 'test';

        $state = new ArrayState();
        $state->setAndLockParameter($parameter, $parameterValue);

        $this->assertSame($parameterValue, $state->getParameter($parameter));
    }

    /**
     * Проверяет, что объект выбросит исключение при попытке изменить
     * заблокированный параметр.
     */
    public function testSetParameterLockedException(): void
    {
        $parameter = StateParameter::TEST;
        $parameterValue = 'test';

        $state = new ArrayState();
        $state->setAndLockParameter($parameter, $parameterValue);

        $this->expectException(\InvalidArgumentException::class);
        $state->setParameter($parameter, $parameterValue);
    }

    /**
     * Проверяем флаг, который мягко прерывает исполнение операций.
     */
    public function testComplete(): void
    {
        $state = new ArrayState();

        $stateCompleted = new ArrayState();
        $stateCompleted->complete();

        $this->assertFalse($state->isCompleted());
        $this->assertTrue($stateCompleted->isCompleted());
    }
}
