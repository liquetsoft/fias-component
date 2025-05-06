<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;

/**
 * Объект, который хранит описания сущностей ФИАС во внутреннем массиве.
 */
final class ArrayEntityRegistry extends AbstractEntityRegistry
{
    /**
     * @var array<int, EntityDescriptor>
     */
    protected array $arrayRegistry;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $registry)
    {
        $this->arrayRegistry = [];

        foreach ($registry as $key => $descriptor) {
            if (!($descriptor instanceof EntityDescriptor)) {
                throw new \InvalidArgumentException(
                    "Item with key {$key} must be an " . EntityDescriptor::class . ' instance.'
                );
            }
            $this->arrayRegistry[] = $descriptor;
        }
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function createRegistry(): array
    {
        return $this->arrayRegistry;
    }
}
