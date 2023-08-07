<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorFile;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadPlanner;
use Liquetsoft\Fias\Component\FiasThread\FiasThreadRunner;
use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAwareTrait;

/**
 * Задача, которая разбивает собранные файлы на части и запускает обработку в отдельных потоках.
 */
final class SplitFilesToThreadsTask implements PipelineTaskLogAware
{
    use PipelineTaskLogAwareTrait;

    public function __construct(
        private readonly FiasThreadPlanner $planner,
        private readonly FiasThreadRunner $runner,
        private readonly int $maxThreadsCount = FiasThreadPlanner::DEFAULT_PROCESS_COUNT
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function run(PipelineState $state): PipelineState
    {
        $filesState = $state->get(PipelineStateParam::FILES_TO_PROCEED);
        $files = [];
        foreach ($filesState as $file) {
            if (!($file instanceof FiasFileSelectorFile)) {
                throw TaskException::create(
                    'All files must be instances of %s',
                    FiasFileSelectorFile::class
                );
            }
            $files[] = $file;
        }

        $threadsParams = array_map(
            fn (array $files): PipelineState => $state->with(PipelineStateParam::FILES_TO_PROCEED, $files),
            $this->planner->plan($files, $this->maxThreadsCount)
        );
        $this->logInfo('Files split to threads');
        $this->runner->run($threadsParams);

        return $state;
    }
}
