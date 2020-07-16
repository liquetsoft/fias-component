<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Parser;

/**
 * Мок для проверки парсера.
 */
class ParserObject
{
    private $strstatid = 0;
    private $name = '';
    private $shortname = '';

    public function setStrstatid(int $strstatid)
    {
        $this->strstatid = $strstatid;
    }

    public function getStrstatid()
    {
        return $this->strstatid;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setShortname(string $shortname)
    {
        $this->shortname = $shortname;
    }

    public function getShortname()
    {
        return $this->shortname;
    }
}
