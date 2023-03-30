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
     * Проверяет, что к объекту можно добавить несколько новых значений.
     */
    public function testWithList(): void
    {
        $interruptValue = true;
        $extractToFolderValue = '/test/';
        $downloadToFileValue = '/test/file.file';
        $state = new PipelineStateArray(
            [
                PipelineStateParam::INTERRUPT_PIPELINE->value => !$interruptValue,
                PipelineStateParam::DOWNLOAD_TO_FILE->value => $downloadToFileValue,
            ]
        );

        $newState = $state->withList(
            [
                PipelineStateParam::INTERRUPT_PIPELINE->value => $interruptValue,
                PipelineStateParam::EXTRACT_TO_FOLDER->value => $extractToFolderValue,
            ]
        );
        $interruptValueToTest = $newState->get(PipelineStateParam::INTERRUPT_PIPELINE);
        $extractToFolderValueToTest = $newState->get(PipelineStateParam::EXTRACT_TO_FOLDER);
        $downloadToFileValueToTest = $newState->get(PipelineStateParam::DOWNLOAD_TO_FILE);

        $this->assertNotSame($state, $newState);
        $this->assertSame($interruptValue, $interruptValueToTest);
        $this->assertSame($extractToFolderValue, $extractToFolderValueToTest);
        $this->assertSame($downloadToFileValue, $downloadToFileValueToTest);
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
