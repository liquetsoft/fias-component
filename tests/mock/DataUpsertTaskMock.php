<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Mock;

/**
 * Мок для проверки задачи об обновлении данных в БД.
 */
class DataUpsertTaskMock
{
    private $actstatid;
    private $name;

    public function setActstatid(int $actstatid)
    {
        $this->actstatid = $actstatid;
    }

    public function getActstatid()
    {
        return $this->actstatid;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
