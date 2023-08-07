<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusChecker;
use Liquetsoft\Fias\Component\FiasStatusChecker\FiasStatusCheckerResult;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;

/**
 * Задача, которая проверяет доступность сервисов ФИАС.
 */
final class FiasStatusCheckTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(private readonly FiasStatusChecker $fiasStatusChecker)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $result = $this->fiasStatusChecker->check();

        if ($result->canProceed()) {
            return $state;
        }

        $this->logInfo(
            'There are some troubles on the FIAS side',
            [
                'statuses' => $this->createLoggableStatuses($result),
            ]
        );

        return $state->with(PipelineStateParam::INTERRUPT_PIPELINE, true);
    }

    /**
     * Создает массив с описаниями статусов по серверам для логгирования.
     *
     * @return array<string, array<string, string>>
     */
    private function createLoggableStatuses(FiasStatusCheckerResult $checkerResult): array
    {
        $result = [];

        foreach ($checkerResult->getPerServiceStatuses() as $serviceStatus) {
            $result[$serviceStatus->getService()->value] = [
                'status' => $serviceStatus->getStatus()->value,
                'reason' => $serviceStatus->getReason(),
            ];
        }

        return $result;
    }
}
