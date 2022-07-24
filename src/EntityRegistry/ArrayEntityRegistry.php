<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;

/**
 * Объект, который хранит описания сущностей ФИАС во внутреннем массиве.
 */
class ArrayEntityRegistry extends AbstractEntityRegistry
{
    /**
     * @var array<int, EntityDescriptor>
     */
    protected array $arrayRegistry;

    /**
     * @param array $registry
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $registry)
    {
        $this->arrayRegistry = [];

        foreach ($registry as $key => $descriptor) {
            if (!($descriptor instanceof EntityDescriptor)) {
                throw new InvalidArgumentException(
                    "Item with key {$key} must be an " . EntityDescriptor::class . ' instance.'
                );
            }
            $this->arrayRegistry[] = $descriptor;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createRegistry(): array
    {
        return $this->arrayRegistry;
    }
}
