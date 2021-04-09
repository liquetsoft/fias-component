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
     * Получает ссылку на файл с полными данными ФИАС.
     *
     * @return InformerResponse
     *
     * @throws FiasInformerException
     */
    public function getCompleteInfo(): InformerResponse;

    /**
     * Получает ссылку на файл с разницей между двумя версиями ФИАС.
     *
     * Возвращает ссылку на файл с изменениями для следующей версии,
     * относительно указанной. Если требуется получить все файлы с изменениями
     * до последней версии ФИАС, то нужно запрашивать данный метод в цикле,
     * изменяя версию, до тех пор, пока он не перестанет возвращать результат.
     *
     * @param int $currentVersion Текущая версия, относительно которой нужно получить файл с изменениями на следующую версию
     *
     * @return InformerResponse
     *
     * @throws FiasInformerException
     */
    public function getDeltaInfo(int $currentVersion): InformerResponse;

    /**
     * Возвращает список всех версий, доступных для установки обновления.
     *
     * @return InformerResponse[]
     *
     * @throws FiasInformerException
     */
    public function getDeltaList(): array;
}
