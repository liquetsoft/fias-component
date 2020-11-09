<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Exception;

use Liquetsoft\Fias\Component\Pipeline\Task\Task;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Throwable;

/**
 * Исключение, которое выбрасывается в случае возникновения ошибки
 * при проведении цепочки операций.
 */
class PipeException extends Exception
{
    /**
     * @var Task
     */
    protected $interrupted_task;

    /**
     * @var Task[]
     */
    protected $pipe_tasks;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param Task   $interrupted_task
     * @param Task[] $pipe_tasks
     * @param State  $state
     *
     * @inheritdoc
     */
    public function __construct(
        $interrupted_task,
        $pipe_tasks,
        $state,
        $message = "",
        $code = 0,
        Throwable $previous = null
    ) {
        $this->interrupted_task = $interrupted_task;
        $this->pipe_tasks = $pipe_tasks;
        $this->state = $state;
    
        parent::__construct($message, $code, $previous);
    }

    public function getTasks()
    {
        return $this->pipe_tasks;
    }

    public function getInterruptedTask()
    {
        return $this->interrupted_task;
    }

    public function getState()
    {
        return $this->state;
    }
}
