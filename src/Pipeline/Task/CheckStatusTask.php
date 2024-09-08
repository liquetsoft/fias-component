<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\StatusCheckerException;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusChecker;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Psr\Log\LogLevel;

/**
 * Задача, которая проверяет статус ФИАС.
 */
final class CheckStatusTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    public function __construct(private readonly FiasStatusChecker $checker)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $status = $this->checker->check();

        if (!$status->canProceed()) {
            $message = 'There are some troubles on the FIAS side. Please try again later.';
            $this->log(
                LogLevel::ERROR,
                $message,
                [
                    'services_statuses' => $status->getPerServiceStatuses(),
                ]
            );
            throw new StatusCheckerException($message);
        }
    }
}
