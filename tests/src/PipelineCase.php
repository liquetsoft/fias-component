<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Liquetsoft\Fias\Component\Pipeline\PipelineState;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Pipeline\PipelineTask;
use Liquetsoft\Fias\Component\Pipeline\PipelineTaskLogAware;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Трэйт, который содержит методы для создания моков пайплайна.
 */
trait PipelineCase
{
    /**
     * Создает мок для объекта состояния.
     *
     * @return PipelineState&MockObject
     */
    public function createPipelineStateMock(array $params = []): PipelineState
    {
        /** @var PipelineState&MockObject */
        $state = $this->getMockBuilder(PipelineState::class)->getMock();

        $state->method('get')->willReturnCallback(
            fn (PipelineStateParam $p): mixed => $params[$p->value] ?? null
        );

        return $state;
    }

    /**
     * Создает мок для объекта задачи.
     *
     * @return PipelineTask&MockObject
     */
    public function createPipelineTaskMock(array $params = []): PipelineTask
    {
        /** @var PipelineTask&MockObject */
        $task = $this->getMockBuilder(PipelineTask::class)->getMock();

        return $task;
    }

    /**
     * Создает мок для объекта задачи с возможностью логгирования.
     *
     * @return PipelineTaskLogAware&MockObject
     */
    public function createPipelineTaskLogAwareMock(array $params = []): PipelineTaskLogAware
    {
        /** @var PipelineTaskLogAware&MockObject */
        $task = $this->getMockBuilder(PipelineTaskLogAware::class)->getMock();

        return $task;
    }
}
