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
final class ArrayStateTest extends BaseCase
{
    /**
     * Проверяет, что конструктор не позволит задачать параметр с неправильным именем.
     */
    public function testConstructWrongNameException(): void
    {
        $parameter = 'qwe';
        $parameterValue = 123;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($parameter);
        new ArrayState(
            [
                $parameter => $parameterValue,
            ]
        );
    }

    /**
     * Проверяет запись параметра.
     */
    public function testSetParameter(): void
    {
        $parameter = StateParameter::FIAS_VERSION_NUMBER;
        $paramValue = new \stdClass();

        $state = new ArrayState();
        $newState = $state->setParameter($parameter, $paramValue);
        $newStateParamValue = $newState->getParameter($parameter);

        $this->assertNotSame($state, $newState);
        $this->assertSame($paramValue, $newStateParamValue);
    }

    /**
     * Проверяет прерывание исполнения операций.
     */
    public function testComplete(): void
    {
        $state = new ArrayState();
        $newState = $state->complete();
        $isCompleted = $newState->isCompleted();

        $this->assertNotSame($state, $newState);
        $this->assertTrue($isCompleted);
    }

    /**
     * Проверяет получение параметра.
     */
    public function testGetParameter(): void
    {
        $parameter = StateParameter::FIAS_VERSION_NUMBER;
        $parameterValue = new \stdClass();

        $state = new ArrayState(
            [
                $parameter->value => $parameterValue,
            ]
        );
        $res = $state->getParameter($parameter);

        $this->assertSame($parameterValue, $res);
    }

    /**
     * Проверяет, что объект вернет значение по умолчанию, если параметр не указан.
     */
    public function testGetParameterDefault(): void
    {
        $parameter = StateParameter::FIAS_VERSION_NUMBER;
        $defaultValue = 123;

        $state = new ArrayState();
        $res = $state->getParameter($parameter, $defaultValue);

        $this->assertSame($defaultValue, $res);
    }

    /**
     * Проверяет получение int параметра.
     */
    public function testGetParameterInt(): void
    {
        $parameter = StateParameter::FIAS_VERSION_NUMBER;
        $parameterValue = '123';

        $state = new ArrayState(
            [
                $parameter->value => $parameterValue,
            ]
        );
        $res = $state->getParameterInt($parameter);

        $this->assertSame((int) $parameterValue, $res);
    }

    /**
     * Проверяет получение string параметра.
     */
    public function testGetParameterString(): void
    {
        $parameter = StateParameter::FIAS_VERSION_NUMBER;
        $parameterValue = 123;

        $state = new ArrayState(
            [
                $parameter->value => $parameterValue,
            ]
        );
        $res = $state->getParameterString($parameter);

        $this->assertSame((string) $parameterValue, $res);
    }

    /**
     * Проверяет флаг, который мягко прерывает исполнение операций.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsCompleted')]
    public function testIsCompleted(bool $isCompleted): void
    {
        $state = new ArrayState(isCompleted: $isCompleted);
        $res = $state->isCompleted();

        $this->assertSame($isCompleted, $res);
    }

    public static function provideIsCompleted(): array
    {
        return [
            'is completed' => [
                true,
            ],
            'is not completed' => [
                false,
            ],
        ];
    }
}
