<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Parallel;

use Closure;

/**
 * Объект DTO для передачи задачи в пулл параллельного выполнения.
 */
class ParallelTask implements Task
{
    /**
     * @var Closure
     */
    protected $closure;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var int|null
     */
    protected $threadNumber;

    /**
     * @param Closure  $closure
     * @param array    $params
     * @param int|null $threadNumber
     */
    public function __construct(Closure $closure, array $params = [], ?int $threadNumber = null)
    {
        $this->closure = $closure;
        $this->params = $params;
        $this->threadNumber = $threadNumber;
    }

    /**
     * @inheritDoc
     */
    public function getClosure(): Closure
    {
        return $this->closure;
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @inheritDoc
     */
    public function getThreadNumber(): ?int
    {
        return $this->threadNumber;
    }
}
