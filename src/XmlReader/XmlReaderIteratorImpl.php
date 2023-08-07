<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\XmlReader;

use Liquetsoft\Fias\Component\Exception\XmlException;

/**
 * Объект, который читает данные из xml файла с помощью XmlReader.
 *
 * @internal
 */
final class XmlReaderIteratorImpl implements XmlReaderIterator
{
    private readonly \XMLReader $phpXmlReader;

    private readonly string $xpath;

    private bool $inUse = false;

    private int $position = 0;

    private ?string $buffer = null;

    public function __construct(\XMLReader $phpXmlReader, string $xpath)
    {
        $this->phpXmlReader = $phpXmlReader;
        $this->xpath = $this->checkAndPrepareXpath($xpath);
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->phpXmlReader->close();
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function rewind(): void
    {
        $this->resetReader();
        $this->buffer = $this->getLine();
    }

    /**
     * {@inheritdoc}
     */
    public function current(): string
    {
        return $this->buffer ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     *
     * @throws XmlException
     */
    public function next(): void
    {
        ++$this->position;
        $this->buffer = $this->getLine();
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        if ($this->buffer === null) {
            $this->phpXmlReader->close();

            return false;
        }

        return true;
    }

    /**
     * Создает новый объект для чтения xml и устанавливает указатель в начало списка.
     */
    private function resetReader(): void
    {
        if ($this->inUse) {
            throw XmlException::create("This iterator can't be rewinded");
        }
        $this->inUse = true;
        $this->seekXmlRoot();
    }

    /**
     * Ищет узел заданный в описании сущности, прежде, чем начать перебор
     * элементов.
     *
     * Если собранный путь лежит в начале строки, которую мы ищем,
     * то продолжаем поиск.
     * Если собранный путь совпадает с тем, что мы ищем,
     * то выходим из цикла.
     * Если путь не совпадает и не лежит в начале строки,
     * то пропускаем данный узел со всеми вложенными деревьями.
     */
    private function seekXmlRoot(): void
    {
        $path = trim($this->xpath, '/');
        $currentPath = [];
        try {
            $readResult = $this->phpXmlReader->read();
            while ($readResult) {
                array_push($currentPath, $this->phpXmlReader->name);
                $currentPathStr = implode('/', $currentPath);
                if ($path === $currentPathStr) {
                    $readResult = false;
                } elseif (strpos($path, $currentPathStr) !== 0) {
                    array_pop($currentPath);
                    $readResult = $this->phpXmlReader->next();
                } else {
                    $readResult = $this->phpXmlReader->read();
                }
            }
        } catch (\Throwable $e) {
            throw XmlException::wrap($e);
        }
    }

    /**
     * Возвращает строку из файла, соответствующую элементу, или null, если разбор
     * файла завершен.
     *
     * @throws XmlException
     */
    private function getLine(): ?string
    {
        $return = null;

        $arPath = explode('/', $this->xpath);
        $nameFilter = array_pop($arPath);
        $currentDepth = $this->phpXmlReader->depth;
        try {
            $this->skipUselessXml($nameFilter, $currentDepth);
            // мы можем выйти из цикла, если найдем нужный элемент
            // или попадем на уровень выше - проверяем, что нашли нужный
            if ($nameFilter === $this->phpXmlReader->name) {
                $return = $this->phpXmlReader->readOuterXml();
                // нужно передвинуть указатель, чтобы дважды не прочитать
                // один и тот же элемент
                $this->phpXmlReader->next();
            }
        } catch (\Throwable $e) {
            throw XmlException::wrap($e);
        }

        return $return;
    }

    /**
     * Пропускает все xml элементы в текущем ридере, у которых имя или вложенность
     * не совпадают с указанным параметром.
     */
    private function skipUselessXml(string $nodeName, int $nodeDepth): void
    {
        while (
            $this->phpXmlReader->depth === $nodeDepth
            && $nodeName !== $this->phpXmlReader->name
            && $this->phpXmlReader->next()
        ) {
            continue;
        }
    }

    /**
     * Проверяет и приводит к общему виду строку с xpath.
     */
    private function checkAndPrepareXpath(string $rawXpath): string
    {
        $xpath = trim($rawXpath);

        if (!str_starts_with($xpath, '/') || \strlen($xpath) <= 1) {
            throw XmlException::create("Xpath parameter can't be empty and must start with '/'");
        }

        return $xpath;
    }
}
