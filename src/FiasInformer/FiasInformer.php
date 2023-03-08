<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasInformer;

use Liquetsoft\Fias\Component\Exception\FiasInformerException;

/**
 * Интерфейс для объекта, который получает ссылку на файл с архивом ФИАС
 * от сервиса информирования ФИАС.
 */
interface FiasInformer
{
    /**
     * Возвращает информацию о последней версии ФИАС.
     *
     * @throws FiasInformerException
     */
    public function getLatestVersion(): InformerResponse;

    /**
     * Получает информацию о версии, которая следует за указанной версией.
     *
     * @throws FiasInformerException
     */
    public function getNextVersion(int|InformerResponse $currentVersion): ?InformerResponse;

    /**
     * Возвращает список всех версий, доступных для установки обновления.
     *
     * @return InformerResponse[]
     *
     * @throws FiasInformerException
     */
    public function getAllVersions(): array;
}
