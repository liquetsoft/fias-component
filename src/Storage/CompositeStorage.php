<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Storage;

use InvalidArgumentException;

/**
 * Объект, который может сохранять данные в несколько других хранилищ.
 */
class CompositeStorage implements Storage
{
    /**
     * @var Storage[]
     */
    protected $internalStorages = [];

    /**
     * @param iterable $internalStorages
     */
    public function __construct(iterable $internalStorages)
    {
        foreach ($internalStorages as $key => $storage) {
            if (!($storage instanceof Storage)) {
                throw new InvalidArgumentException(
                    "Item with key '{$key}' must be instance of '" . Storage::class . "'."
                );
            }
            $this->internalStorages[] = $storage;
        }
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        foreach ($this->internalStorages as $storage) {
            $storage->start();
        }
    }

    /**
     * @inheritDoc
     */
    public function stop(): void
    {
        foreach ($this->internalStorages as $storage) {
            $storage->stop();
        }
    }

    /**
     * @inheritDoc
     */
    public function supports(object $entity): bool
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
     * @inheritDoc
     */
    public function supportsClass(string $class): bool
    {
        $isSupport = false;
        foreach ($this->internalStorages as $storage) {
            if ($storage->supportsClass($class)) {
                $isSupport = true;
                break;
            }
        }

        return $isSupport;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function truncate(string $entityClassName): void
    {
        foreach ($this->internalStorages as $storage) {
            if (!$storage->supportsClass($entityClassName)) {
                continue;
            }
            $storage->truncate($entityClassName);
        }
    }
}
