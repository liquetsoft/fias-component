<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use InvalidArgumentException;

/**
 * Объект, который получает хранит описания сущностей ФИАС во внутреннем массиве.
 */
class ArrayEntityRegistry extends AbstractEntityRegistry
{
    /**
     * @param array $registry
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $registry)
    {
        $this->registry = [];

        foreach ($registry as $key => $descriptor) {
            if (!($descriptor instanceof EntityDescriptor)) {
                throw new InvalidArgumentException(
                    "Item with key {$key} must be an " . EntityDescriptor::class . ' instance.'
                );
            }
            $this->registry[] = $descriptor;
        }
    }

    /**
     * @inheritdoc
     */
    protected function createRegistry(): array
    {
        return [];
    }
}
