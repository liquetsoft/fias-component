<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\VersionManager;

use Liquetsoft\Fias\Component\FiasInformer\FiasInformerResponse;

/**
 * Интерфейс для объекта, который хранит в себе текущую версию ФИАС, установленную
 * в локальном хранилище.
 */
interface VersionManager
{
    /**
     * Задает версию ФИАС из ответа от сервиса информирования ФИАС.
     */
    public function setCurrentVersion(FiasInformerResponse $info): void;

    /**
     * Возвращает текущую версию ФИАС.
     */
    public function getCurrentVersion(): ?FiasInformerResponse;
}
