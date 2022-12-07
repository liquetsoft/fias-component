<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Mock;

/**
 * Мок для тестирования сериализатора.
 *
 * @internal
 */
class FiasSerializerMock
{
    /**
     * @var int
     */
    private $ACTSTATID = 0;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var \DateTimeInterface|null
     */
    private $testDate = null;

    /**
     * @var string
     */
    private $kodtst = '';

    /**
     * @var int
     */
    private $emptyStringInt = 0;

    public function setActstatid(int $ACTSTATID): void
    {
        $this->ACTSTATID = $ACTSTATID;
    }

    public function getActstatid(): int
    {
        return $this->ACTSTATID;
    }

    public function setName(string $NAME): void
    {
        $this->name = $NAME;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setTestDate(\DateTimeInterface $testDate): void
    {
        $this->testDate = $testDate;
    }

    public function getTestDate(): ?\DateTimeInterface
    {
        return $this->testDate;
    }

    public function setKodtst(string $kodtst): void
    {
        $this->kodtst = $kodtst;
    }

    public function getKodtst(): string
    {
        return $this->kodtst;
    }

    public function setEmptyStringInt(int $emptyStringInt): void
    {
        $this->emptyStringInt = $emptyStringInt;
    }

    public function getEmptyStringInt(): int
    {
        return $this->emptyStringInt;
    }
}
