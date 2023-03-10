<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityRegistry;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;

/**
 * Объект, который хранит описания сущностей ФИАС во внутреннем массиве.
 */
class ArrayEntityRegistry extends AbstractEntityRegistry
{
    /**
     * @var array<int, FiasEntity>
     */
    protected array $arrayRegistry;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $registry)
    {
        $this->arrayRegistry = [];

        foreach ($registry as $key => $descriptor) {
            if (!($descriptor instanceof FiasEntity)) {
                throw new \InvalidArgumentException(
                    "Item with key {$key} must be an " . FiasEntity::class . ' instance.'
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
