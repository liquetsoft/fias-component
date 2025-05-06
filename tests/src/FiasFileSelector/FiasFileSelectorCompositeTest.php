<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasFileSelector;

use Liquetsoft\Fias\Component\FiasFile\FiasFile;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelector;
use Liquetsoft\Fias\Component\FiasFileSelector\FiasFileSelectorComposite;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который содержит несколько вложенных объектов для выбора файлов.
 *
 * @internal
 */
final class FiasFileSelectorCompositeTest extends BaseCase
{
    /**
     * Проверяет, что объект определит подходит ли источник данных.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSupportSource')]
    public function testSupportSource(array $nestedSelectorsResults, bool $expected): void
    {
        $source = $this->mock(\SplFileInfo::class);

        $nestedSelectors = [];
        foreach ($nestedSelectorsResults as $nestedSelectorsResult) {
            $selector = $this->mock(FiasFileSelector::class);
            $selector->expects($this->any())
                ->method('supportSource')
                ->with(
                    $this->identicalTo($source)
                )
                ->willReturn($nestedSelectorsResult);
            $nestedSelectors[] = $selector;
        }

        $selector = new FiasFileSelectorComposite($nestedSelectors);
        $res = $selector->supportSource($source);

        $this->assertSame($expected, $res);
    }

    public static function provideSupportSource(): array
    {
        return [
            'one selctor supports' => [
                [
                    false,
                    true,
                    false,
                ],
                true,
            ],
            'no selectors supports' => [
                [
                    false,
                    false,
                    false,
                ],
                false,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет все файлы, которые подходят.
     */
    public function testSelectFiles(): void
    {
        $source = $this->mock(\SplFileInfo::class);

        $selector = $this->mock(FiasFileSelector::class);
        $selector->expects($this->any())->method('supportSource')->willReturn(false);
        $selector->expects($this->never())->method('selectFiles');

        $selector1Res = [
            $this->mock(FiasFile::class),
            $this->mock(FiasFile::class),
        ];
        $selector1 = $this->mock(FiasFileSelector::class);
        $selector1->expects($this->once())
            ->method('supportSource')
            ->with(
                $this->identicalTo($source)
            )
            ->willReturn(true);
        $selector1->expects($this->once())
            ->method('selectFiles')
            ->with(
                $this->identicalTo($source)
            )
            ->willReturn($selector1Res);

        $selector = new FiasFileSelectorComposite([$selector, $selector1]);
        $res = $selector->selectFiles($source);

        $this->assertSame($selector1Res, $res);
    }

    /**
     * Проверяет, что объект вернет все файлы, которые подходят.
     */
    public function testSelectFilesNotSupportedSource(): void
    {
        $source = $this->mock(\SplFileInfo::class);

        $selector = $this->mock(FiasFileSelector::class);
        $selector->expects($this->any())->method('supportSource')->willReturn(false);
        $selector->expects($this->never())->method('selectFiles');

        $selector1 = $this->mock(FiasFileSelector::class);
        $selector1->expects($this->any())->method('supportSource')->willReturn(false);
        $selector1->expects($this->never())->method('selectFiles');

        $selector = new FiasFileSelectorComposite([$selector, $selector1]);
        $res = $selector->selectFiles($source);

        $this->assertSame([], $res);
    }
}
