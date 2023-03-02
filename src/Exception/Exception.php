<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Exception;

/**
 * Исключение, от которого наследуются все исключения библиотеки.
 *
 * @psalm-consistent-constructor
 */
class Exception extends \Exception
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Фабричный метод, который собирает сообщение для исключения, используя sprintf.
     *
     * @param mixed[] $params
     */
    public static function create(string $message, ...$params): static
    {
        $params = array_map(fn (mixed $param): string => (string) $param, $params);

        array_unshift($params, $message);

        /** @var string */
        $compiledMessage = \call_user_func_array('sprintf', $params);

        return new static($compiledMessage);
    }
}
