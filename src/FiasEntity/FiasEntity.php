<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;

/**
 * Интерфейс для объекта, который описывает сущность ФИАС.
 */
interface FiasEntity
{
    /**
     * Возвращает имя сущности.
     */
    public function getName(): string;

    /**
     * Возвращает описание сущности.
     */
    public function getDescription(): string;

    /**
     * Возвращает количество частей, на которое нужно разбить таблицу.
     */
    public function getPartitionsCount(): int;

    /**
     * Возвращает xpath к сущности в xml файле.
     */
    public function getXmlPath(): string;

    /**
     * Возвращает маску xml файла, в котором содержатся данные для вставки.
     */
    public function getXmlInsertFileMask(): string;

    /**
     * Возвращает маску xml файла, в котором содержатся данные для удаления.
     */
    public function getXmlDeleteFileMask(): string;

    /**
     * Возвращает список полей для данной сущности.
     *
     * @return iterable<FiasEntityField>
     */
    public function getFields(): iterable;

    /**
     * Проверяет существует ли поле с указанным именем.
     */
    public function hasField(string $name): bool;

    /**
     * Возвращает поле по имени или выбрасывает исключение, если поля с таким именем нет.
     *
     * @throws FiasEntityException
     */
    public function getField(string $name): FiasEntityField;

    /**
     * Проверяет подходит ли имя файла для загрузки данных.
     */
    public function isFileNameFitsXmlInsertFileMask(string $fileName): bool;

    /**
     * Проверяет подходит ли имя файла для удаления данных.
     */
    public function isFileNameFitsXmlDeleteFileMask(string $fileName): bool;
}
