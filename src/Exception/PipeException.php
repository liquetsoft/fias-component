<?php
declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Exception;

use Liquetsoft\Fias\Component\Pipeline\Task\Task;
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
    protected $interruptedTask;

    /**
     * @param Task   $interrupted_task
     *
     * @inheritdoc
     */
    public function __construct(
        $interrupted_task,
        $message = "",
        $code = 0,
        Throwable $previous = null
    ) {
        $this->interruptedTask = $interrupted_task;
        parent::__construct($message, $code, $previous);
    }

    public function getInterruptedTask()
    {
        return $this->interruptedTask;
    }
}
