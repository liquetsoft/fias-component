<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Liquetsoft\Fias\Component\XmlReader\XmlReaderIterator;
use Liquetsoft\Fias\Component\XmlReader\XmlReaderProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Трэйт, который содержит методы для создания моков объекта, обрабатывающего xml.
 */
trait XmlReaderCase
{
    /**
     * Создает мок для объекта, который открывает xml файлы, с массивом данных для итерации.
     *
     * @return XmlReaderProvider&MockObject
     */
    protected function createXmlReaderProviderMockIterator(string $fileName, string $xPath, array $stringsToRead): XmlReaderProvider
    {
        $it = (new \ArrayObject($stringsToRead))->getIterator();
        $iterator = $this->getMockBuilder(XmlReaderIterator::class)->getMock();
        $iterator->method('rewind')->willReturnCallback(function () use ($it): void { $it->rewind(); });
        $iterator->method('next')->willReturnCallback(function () use ($it): void { $it->next(); });
        $iterator->method('current')->willReturnCallback(fn (): mixed => $it->current());
        $iterator->method('key')->willReturnCallback(fn (): mixed => $it->key());
        $iterator->method('valid')->willReturnCallback(fn (): bool => $it->valid());
        $iterator->expects($this->once())->method('close');

        $provider = $this->createXmlReaderProviderMock();
        $provider->expects($this->once())
            ->method('open')
            ->with(
                $this->callback(
                    fn (\SplFileInfo $f): bool => $f->getPathname() === $fileName
                ),
                $this->identicalTo($xPath)
            )
            ->willReturn($iterator);

        return $provider;
    }

    /**
     * Создает мок для объекта, который открывает xml файлы.
     *
     * @return XmlReaderProvider&MockObject
     */
    protected function createXmlReaderProviderMock(): XmlReaderProvider
    {
        /** @var XmlReaderProvider&MockObject */
        $provider = $this->getMockBuilder(XmlReaderProvider::class)->getMock();

        return $provider;
    }
}
