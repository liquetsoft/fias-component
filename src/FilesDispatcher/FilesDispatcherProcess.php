<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FilesDispatcher;

/**
 * Вспомогательный объект для представления процесса в диспетчере.
 */
class FilesDispatcherProcess
{
    /**
     * @var string[]
     */
    private $items = [];

    /**
     * @var int
     */
    private $weight = 0;

    public function addItem(string $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return string[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addWeight(int $weight): void
    {
        $this->weight += $weight;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }
}
