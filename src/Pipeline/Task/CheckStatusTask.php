<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\StatusCheckerException;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusChecker;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerStatus;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Psr\Log\LogLevel;

/**
 * Задача, которая проверяет статус ФИАС.
 */
class CheckStatusTask implements LoggableTask, Task
{
    use LoggableTaskTrait;

    protected FiasStatusChecker $checker;

    public function __construct(FiasStatusChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * {@inheritDoc}
     */
    public function run(State $state): void
    {
        $status = $this->checker->check();

        if ($status->getResultStatus() !== FiasStatusCheckerStatus::AVAILABLE) {
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
