<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformer;
use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\StateParameter;
use Liquetsoft\Fias\Component\Pipeline\Task\InformDeltaTask;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для задачи, которая получает ссылку на частичную версию ФИАС.
 *
 * @internal
 */
final class InformDeltaTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно получает ссылку.
     */
    public function testRun(): void
    {
        $oldVersion = 122;
        $version = 123;
        $fullUrl = 'https://test.test/full';
        $deltaUrl = 'https://test.test/delta';

        $informerResult = $this->mock(FiasInformerResponse::class);
        $informerResult->expects($this->any())->method('getVersion')->willReturn($version);
        $informerResult->expects($this->any())->method('getDeltaUrl')->willReturn($deltaUrl);
        $informerResult->expects($this->any())->method('getFullUrl')->willReturn($fullUrl);

        $informer = $this->mock(FiasInformer::class);
        $informer->expects($this->once())
            ->method('getNextVersion')
            ->with(
                $this->equalTo($oldVersion)
            )
            ->willReturn($informerResult);

        $state = $this->createStateMock(
            [
                StateParameter::FIAS_VERSION_NUMBER->value => $oldVersion,
            ]
        );

        $task = new InformDeltaTask($informer);
        $newState = $task->run($state);
        $resVersion = $newState->getParameter(StateParameter::FIAS_NEXT_VERSION_NUMBER);
        $resUrl = $newState->getParameter(StateParameter::FIAS_VERSION_ARCHIVE_URL);
        $resFullUrl = $newState->getParameter(StateParameter::FIAS_NEXT_VERSION_FULL_URL);
        $resDeltaUrl = $newState->getParameter(StateParameter::FIAS_NEXT_VERSION_DELTA_URL);

        $this->assertSame($version, $resVersion);
        $this->assertSame($deltaUrl, $resUrl);
        $this->assertSame($fullUrl, $resFullUrl);
        $this->assertSame($deltaUrl, $resDeltaUrl);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указана текущая версия ФИАС.
     */
    public function testRunNoVersionException(): void
    {
        $informer = $this->mock(FiasInformer::class);

        $state = $this->createStateMock();

        $task = new InformDeltaTask($informer);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект прервет цепочку задач, если не найдется обновлений.
     */
    public function testRunNoResponseComplete(): void
    {
        $version = 123;

        $informer = $this->mock(FiasInformer::class);
        $informer->expects($this->any())
            ->method('getNextVersion')
            ->with(
                $this->identicalTo($version)
            )
            ->willReturn(null);

        $state = $this->createStateMock(
            [
                StateParameter::FIAS_VERSION_NUMBER->value => $version,
            ]
        );

        $task = new InformDeltaTask($informer);
        $newState = $task->run($state);
        $resVersion = $newState->getParameter(StateParameter::FIAS_NEXT_VERSION_NUMBER);
        $resUrl = $newState->getParameter(StateParameter::FIAS_VERSION_ARCHIVE_URL);
        $resIsCompleted = $newState->isCompleted();

        $this->assertNull($resVersion);
        $this->assertNull($resUrl);
        $this->assertTrue($resIsCompleted);
    }
}
