<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\EntityManager;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;

/**
 * Интерфейс для объекта, который содержит соответствия между сущностями ФИАС и их
 * реализациями в конкретном проекте.
 */
interface EntityManager
{
    /**
     * Возвращает дескриптор для сущности по имени сущности.
     *
     * @param string $entityName
     *
     * @return EntityDescriptor|null
     */
    public function getDescriptorByEntityName(string $entityName): ?EntityDescriptor;

    /**
     * Ищет класс реализации сущности ФИАС для указанного дескриптора.
     *
     * @param EntityDescriptor $descriptor
     *
     * @return string|null
     */
    public function getClassByDescriptor(EntityDescriptor $descriptor): ?string;

    /**
     * Возвращает дескриптор сущности, которая соответствует файлу с данными для загрузки.
     *
     * @param string $insertFileName
     *
     * @return EntityDescriptor|null
     */
    public function getDescriptorByInsertFile(string $insertFileName): ?EntityDescriptor;

    /**
     * Возвращает дескриптор сущности, которая соответствует файлу с данными для удаления.
     *
     * @param string $insertFileName
     *
     * @return EntityDescriptor|null
     */
    public function getDescriptorByDeleteFile(string $insertFileName): ?EntityDescriptor;

    /**
     * Возвращает дескриптор сущности, к которой относится указанный класс.
     *
     * @param string $className
     *
     * @return EntityDescriptor|null
     */
    public function getDescriptorByClass(string $className): ?EntityDescriptor;

    /**
     * Возвращает дескриптор сущности, к которой относится указанный объект.
     *
     * @param mixed $object
     *
     * @return EntityDescriptor|null
     */
    public function getDescriptorByObject($object): ?EntityDescriptor;
}
