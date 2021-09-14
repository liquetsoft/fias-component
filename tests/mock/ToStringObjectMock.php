<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Mock;

/**
 * Мок для объектов, которые могут быть преобразованы к строке.
 *
 * @internal
 */
class ToStringObjectMock
{
    /**
     * @var string
     */
    private $data;

    public function __construct(string $data = '')
    {
        $this->data = $data;
    }

    public function __toString(): string
    {
        return $this->data;
    }
}
