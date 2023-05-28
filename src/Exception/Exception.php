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
    public const DEFAULT_CODE = 0;

    public function __construct(string $message = '', int|string $code = self::DEFAULT_CODE, \Throwable $previous = null)
    {
        parent::__construct($message, (int) $code, $previous);
    }

    /**
     * Фабричный метод, который собирает сообщение для исключения, используя sprintf.
     *
     * @param mixed[] $params
     */
    public static function create(string $message, ...$params): static
    {
        $params = array_map(fn (mixed $param): string => trim((string) $param), $params);

        array_unshift($params, $message);

        /** @var string */
        $compiledMessage = \call_user_func_array('sprintf', $params);

        return new static($compiledMessage);
    }

    /**
     * Фабричный метод, который оборачивает готовое исключение другим.
     *
     * @psalm-suppress PossiblyInvalidArgument
     */
    public static function wrap(\Throwable $e): static
    {
        return $e instanceof static
            ? $e
            : new static($e->getMessage(), $e->getCode(), $e);
    }
}
