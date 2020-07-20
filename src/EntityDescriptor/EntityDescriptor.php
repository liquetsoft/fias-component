<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityDescriptor;

use InvalidArgumentException;
use Liquetsoft\Fias\Component\EntityField\EntityField;

/**
 * Интерфейс для объекта, который описывает сущность ФИАС.
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
     * Возвращает параметры к сущности Render.
     * @param string $type
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function getReaderParams(string $type);
    

    /**
     * Возвращает маску файла, в котором содержатся данные для вставки.
     *
     * @return string
     */
    public function getInsertFileMask(): string;

    /**
     * Возвращает маску файла, в котором содержатся данные для удаления.
     *
     * @return string
     */
    public function getDeleteFileMask(): string;

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
    public function isFileNameMatchInsertFileMask(string $fileName): bool;

    /**
     * Проверяет подходит ли имя файла для удаления данных.
     *
     * @param string $fileName
     *
     * @return bool
     */
    public function isFileNameMatchDeleteFileMask(string $fileName): bool;
}
