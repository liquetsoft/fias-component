<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\FiasFileSelector;

/**
 * Объект, который содержит несколько вложенных объектов для выбора файлов.
 * Передает управление первому объекту, который поддерживает источник данных.
 */
final class FiasFileSelectorComposite implements FiasFileSelector
{
    /**
     * @param iterable<FiasFileSelector> $filesSelectors
     */
    public function __construct(private readonly iterable $filesSelectors)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function supportSource(\SplFileInfo $source): bool
    {
        foreach ($this->filesSelectors as $selector) {
            if ($selector->supportSource($source)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function selectFiles(\SplFileInfo $source): array
    {
        foreach ($this->filesSelectors as $selector) {
            if ($selector->supportSource($source)) {
                return $selector->selectFiles($source);
            }
        }

        return [];
    }
}
