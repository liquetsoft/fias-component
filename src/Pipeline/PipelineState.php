<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

/**
 * Интерфейс для объекта, который передает состояние между операциями.
 */
interface PipelineState
{
    /**
     * Создает новый объект состояния с дополнительным/обновленным значением параметра.
     */
    public function with(PipelineStateParam $param, mixed $value): static;

    /**
     * Создает новый объект состояния с дополнительными/обновленными параметрами.
     *
     * @param array<string, mixed> $params
     */
    public function withList(array $params): static;

    /**
     * Создает новый объект состояния без указанного параметра.
     */
    public function without(PipelineStateParam $param): static;

    /**
     * Возвращает параметр состояния по его имени.
     */
    public function get(PipelineStateParam $param): mixed;
}
