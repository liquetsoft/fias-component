<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline;

use Liquetsoft\Fias\Component\Pipeline\PipelineStateArray;
use Liquetsoft\Fias\Component\Pipeline\PipelineStateParam;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который передает состояние между задачами.
 *
 * @internal
 */
class PipelineStateArrayTest extends BaseCase
{
    /**
     * Проверяет, что к объекту можно добавить новое значение.
     */
    public function testWith(): void
    {
        $interruptValue = true;
        $state = new PipelineStateArray([]);

        $newState = $state->with(PipelineStateParam::INTERRUPT_PIPELINE, $interruptValue);
        $interruptValueToTest = $newState->get(PipelineStateParam::INTERRUPT_PIPELINE);

        $this->assertNotSame($state, $newState);
        $this->assertSame($interruptValue, $interruptValueToTest);
    }

    /**
     * Проверяет, что из объекта можно удалить значение.
     */
    public function testWithout(): void
    {
        $interruptValue = true;
        $state = new PipelineStateArray(
            [
                PipelineStateParam::INTERRUPT_PIPELINE->value => $interruptValue,
            ]
        );

        $newState = $state->without(PipelineStateParam::INTERRUPT_PIPELINE);
        $interruptValueToTest = $newState->get(PipelineStateParam::INTERRUPT_PIPELINE);

        $this->assertNotSame($state, $newState);
        $this->assertNull($interruptValueToTest);
    }
}
