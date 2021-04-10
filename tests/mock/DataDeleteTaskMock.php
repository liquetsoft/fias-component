<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Mock;

/**
 * Мок для проверки задачи об удалении данных из БД.
 *
 * @internal
 */
class DataDeleteTaskMock
{
    /**
     * @var int
     */
    private $actstatid = 0;

    /**
     * @var string
     */
    private $name = '';

    public function setActstatid(int $actstatid): void
    {
        $this->actstatid = $actstatid;
    }

    public function getActstatid(): int
    {
        return $this->actstatid;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
