<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityField;

/**
 * Интерфейс для объекта, который описывает поле сущности.
 */
interface EntityField
{
    /**
     * Возвращает имя поля.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает описание поля.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Возвращает тип поля.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Возвращает дополнительную типизацию для основного типа поля.
     *
     * Например, используется, чтобы указать, что в строке не просто строка, а дата
     *
     * @return string
     */
    public function getSubType(): string;

    /**
     * Возвращает длину значения поля.
     *
     * @return int|null
     */
    public function getLength(): ?int;

    /**
     * Возвращает правду, если в значении поля можно указать null.
     *
     * @return bool
     */
    public function isNullable(): bool;

    /**
     * Возвращает правду, если поле используется в качестве primary индекса.
     *
     * @return bool
     */
    public function isPrimary(): bool;

    /**
     * Возвращает правду, если поле используется в качестве индекса.
     *
     * @return bool
     */
    public function isIndex(): bool;

    /**
     * Возвращает правду, если поле используется в качестве индекса для секционирования.
     *
     * @return bool
     */
    public function isPartition(): bool;
}
