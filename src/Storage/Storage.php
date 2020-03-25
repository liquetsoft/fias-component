<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Storage;

use Liquetsoft\Fias\Component\Exception\StorageException;

/**
 * Интерфейс для объекта, который отвечает за доступ к хранилищу данных,
 * например к базе данных.
 */
interface Storage
{
    /**
     * Уведомляет хранилище о начале работы в рамках задачи.
     *
     * @throws StorageException
     */
    public function start(): void;

    /**
     * Уведомляет хранилище о завершении работы в рамках задачи.
     *
     * @throws StorageException
     */
    public function stop(): void;

    /**
     * Проверяет может ли хранилище работать с данным объектом.
     *
     * @param object $entity
     *
     * @return bool
     */
    public function supports(object $entity): bool;

    /**
     * Проверяет может ли хранилище работать с данным типом объектов.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass(string $class): bool;

    /**
     * Отправляет объект на запись в хранилище.
     *
     * @param object $entity
     *
     * @throws StorageException
     */
    public function insert(object $entity): void;

    /**
     * Удаляет объект из хранилища.
     *
     * @param object $entity
     *
     * @throws StorageException
     */
    public function delete(object $entity): void;

    /**
     * Если запись уже имеется в БД, то обновляет ее из объекта, если записи нет,
     * то создает новую.
     *
     * @param object $entity
     *
     * @throws StorageException
     */
    public function upsert(object $entity): void;

    /**
     * Очищает хранилище для объектов с указаным в параметре классом.
     *
     * @param string $entityClassName
     *
     * @throws StorageException
     */
    public function truncate(string $entityClassName): void;
}
