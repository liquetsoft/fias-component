<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\EntityRegistry;

use Liquetsoft\Fias\Component\Exception\Exception;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для кастомных методов исключений, принадлежащих библиотеке.
 *
 * @internal
 */
class ExceptionTest extends BaseCase
{
    public function testConstructStringCode(): void
    {
        $message = 'test';
        $code = '123';

        $exception = new Exception($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame((int) $code, $exception->getCode());
    }

    /**
     * @dataProvider provideCreate
     */
    public function testCreate(string $message, array $params, string $awaitsMessage): void
    {
        $exception = Exception::create($message, ...$params);

        $this->assertSame($awaitsMessage, $exception->getMessage());
    }

    public function provideCreate(): array
    {
        return [
            'simple text message' => ['test', [], 'test'],
            'message with replacement' => ['test %s', ['test'], 'test test'],
            'message with not trimmed replacement' => ['test %s', ['   test   '], 'test test'],
            'message with non-string replacement' => ['test %s', [123], 'test 123'],
        ];
    }

    public function testWrap(): void
    {
        $message = 'message';
        $code = 123;
        $wrappedException = new \RuntimeException($message, $code);

        $exception = Exception::wrap($wrappedException);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($wrappedException, $exception->getPrevious());
    }
}
