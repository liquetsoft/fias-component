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
}
