<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

/**
 * Интерфейс для объекта, который описывает поле сущности.
 */
interface FiasEntityField
{
    /**
     * Возвращает тип поля.
     */
    public function getType(): FiasEntityFieldType;

    /**
     * Возвращает дополнительную типизацию для основного типа поля.
     *
     * Например, используется, чтобы указать, что в строке не просто строка, а дата.
     */
    public function getSubType(): FiasEntityFieldSubType;

    /**
     * Возвращает имя поля.
     */
    public function getName(): string;

    /**
     * Возвращает описание поля.
     */
    public function getDescription(): string;

    /**
     * Возвращает длину значения поля.
     */
    public function getLength(): ?int;

    /**
     * Возвращает правду, если в значении поля можно указать null.
     */
    public function isNullable(): bool;

    /**
     * Возвращает правду, если поле используется в качестве primary индекса.
     */
    public function isPrimary(): bool;

    /**
     * Возвращает правду, если поле используется в качестве индекса.
     */
    public function isIndex(): bool;

    /**
     * Возвращает правду, если поле используется в качестве индекса для секционирования.
     */
    public function isPartition(): bool;
}
