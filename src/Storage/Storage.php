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
     * Проверяет может ли хранилище работать с данным объектом или классом.
     *
     * @psalm-param object|class-string $entity
     */
    public function supports(object|string $entity): bool;

    /**
     * Отправляет объект на запись в хранилище.
     *
     * @throws StorageException
     */
    public function insert(object $entity): void;

    /**
     * Удаляет объект из хранилища.
     *
     * @throws StorageException
     */
    public function delete(object $entity): void;

    /**
     * Если запись уже имеется в БД, то обновляет ее из объекта, если записи нет,
     * то создает новую.
     *
     * @throws StorageException
     */
    public function upsert(object $entity): void;

    /**
     * Очищает хранилище для объектов с указанным в параметре классом.
     *
     * @psalm-param class-string $entityClassName
     *
     * @throws StorageException
     */
    public function truncate(string $entityClassName): void;
}
