<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityDescriptor;

use Liquetsoft\Fias\Component\EntityField\EntityField;
use InvalidArgumentException;

/**
 * Интерфейс для объекта, который описывает сущность.
 */
interface EntityDescriptor
{
    /**
     * Возвращает имя сущности.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает описание сущности.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Возвращает количество частей, на которое нужно разбить таблицу.
     *
     * @return int
     */
    public function getPartitionsCount(): int;

    /**
     * Возвращает xpath к сущности в xml файле.
     *
     * @return string
     */
    public function getXmlPath(): string;

    /**
     * Возвращает маску xml файла, в котором содержатся данные для вставки.
     *
     * @return string
     */
    public function getXmlInsertFileMask(): string;

    /**
     * Возвращает маску xml файла, в котором содержатся данные для удаления.
     *
     * @return string
     */
    public function getXmlDeleteFileMask(): string;

    /**
     * Возвращает список полей для данной сущности.
     *
     * @return EntityField[]
     */
    public function getFields(): array;

    /**
     * Проверяет существует ли поле с указанным именем.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasField(string $name): bool;

    /**
     * Возвращает поле по имени или выбрасывает исключение, если поля с таким именем нет.
     *
     * @param string $name
     *
     * @return EntityField
     *
     * @throws InvalidArgumentException
     */
    public function getField(string $name): EntityField;

    /**
     * Проверяет подходит ли имя файла для загрузки данных.
     *
     * @param string $fileName
     *
     * @return bool
     */
    public function isFileNameFitsXmlInsertFileMask(string $fileName): bool;

    /**
     * Проверяет подходит ли имя файла для удаления данных.
     *
     * @param string $fileName
     *
     * @return bool
     */
    public function isFileNameFitsXmlDeleteFileMask(string $fileName): bool;
}
