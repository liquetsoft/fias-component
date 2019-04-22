<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityDescriptor;

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
}
