<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityDescriptor;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntityField;

/**
 * Интерфейс для объекта, который описывает сущность ФИАС.
 */
interface EntityDescriptor
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
     * @return FiasEntityField[]
     */
    public function getFields(): array;

    /**
     * Проверяет существует ли поле с указанным именем.
     */
    public function hasField(string $name): bool;

    /**
     * Возвращает поле по имени или выбрасывает исключение, если поля с таким именем нет.
     *
     * @throws \InvalidArgumentException
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
