<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Mock;

use Liquetsoft\Fias\Component\Pipeline\Task\LoggableTask;
use Liquetsoft\Fias\Component\Pipeline\Task\Task;

/**
 * Abstract mock class to test task with loggable task interface.
 */
abstract class ArrayPipeTestLoggableMock implements Task, LoggableTask
{
}
