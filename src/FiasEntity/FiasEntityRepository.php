<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;

/**
 * Интерфейс для объекта, который хранит список всех сущностей.
 */
interface FiasEntityRepository
{
    /**
     * Возвращает список всех сущностей.
     *
     * @return iterable<FiasEntity>
     *
     * @throws FiasEntityException
     */
    public function getAllEntities(): iterable;

    /**
     * Проверяет существует ли описание сущности с указанным псевдонимом.
     *
     * @throws FiasEntityException
     */
    public function hasEntity(string $entityName): bool;

    /**
     * Возвращает описание сущности с указанным псевдонимом.
     *
     * @throws FiasEntityException
     */
    public function getEntity(string $entityName): FiasEntity;
}
