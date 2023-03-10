<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Storage;

/**
 * Объект, который может сохранять данные в несколько других хранилищ.
 */
final class StorageComposite implements Storage
{
    /**
     * @var iterable<Storage>
     */
    private readonly iterable $internalStorages;

    /**
     * @param iterable<Storage> $internalStorages
     */
    public function __construct(iterable $internalStorages)
    {
        $this->internalStorages = $internalStorages;
    }

    /**
     * {@inheritDoc}
     */
    public function start(): void
    {
        foreach ($this->internalStorages as $storage) {
            $storage->start();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void
    {
        foreach ($this->internalStorages as $storage) {
            $storage->stop();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports(object|string $entity): bool
    {
        $isSupport = false;
        foreach ($this->internalStorages as $storage) {
            if ($storage->supports($entity)) {
                $isSupport = true;
                break;
            }
        }

        return $isSupport;
    }

    /**
     * {@inheritDoc}
     */
    public function insert(object $entity): void
    {
        foreach ($this->internalStorages as $storage) {
            if (!$storage->supports($entity)) {
                continue;
            }
            $storage->insert($entity);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(object $entity): void
    {
        foreach ($this->internalStorages as $storage) {
            if (!$storage->supports($entity)) {
                continue;
            }
            $storage->delete($entity);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function upsert(object $entity): void
    {
        foreach ($this->internalStorages as $storage) {
            if (!$storage->supports($entity)) {
                continue;
            }
            $storage->upsert($entity);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function truncate(string $entityClassName): void
    {
        foreach ($this->internalStorages as $storage) {
            if (!$storage->supports($entityClassName)) {
                continue;
            }
            $storage->truncate($entityClassName);
        }
    }
}
