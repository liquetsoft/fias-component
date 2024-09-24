<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\ArrayState;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\InformFullTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая получает ссылку на полную версию ФИАС.
 *
 * @internal
 */
final class InformFullTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно получает ссылку.
     */
    public function testRun(): void
    {
        $version = 123;
        $fullUrl = 'https://test.test/full';
        $deltaUrl = 'https://test.test/delta';

        $informerResult = $this->mock(FiasInformerResponse::class);
        $informerResult->expects($this->any())->method('getVersion')->willReturn($version);
        $informerResult->expects($this->any())->method('getDeltaUrl')->willReturn($deltaUrl);
        $informerResult->expects($this->any())->method('getFullUrl')->willReturn($fullUrl);

        $informer = $this->mock(FiasInformer::class);
        $informer->expects($this->any())->method('getLatestVersion')->willReturn($informerResult);

        $state = new ArrayState();

        $task = new InformFullTask($informer);
        $task->run($state);
        $resVersion = $state->getParameter(StateParameter::FIAS_NEXT_VERSION_NUMBER);
        $resUrl = $state->getParameter(StateParameter::FIAS_VERSION_ARCHIVE_URL);
        $resFullUrl = $state->getParameter(StateParameter::FIAS_NEXT_VERSION_FULL_URL);
        $resDeltaUrl = $state->getParameter(StateParameter::FIAS_NEXT_VERSION_DELTA_URL);

        $this->assertSame($version, $resVersion);
        $this->assertSame($fullUrl, $resUrl);
        $this->assertSame($fullUrl, $resFullUrl);
        $this->assertSame($deltaUrl, $resDeltaUrl);
    }
}
