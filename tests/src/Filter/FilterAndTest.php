<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Filter;

use Liquetsoft\Fias\Component\Filter\Filter;
use Liquetsoft\Fias\Component\Filter\FilterAnd;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для фильтра, объединяющего несколько фильтров через AND.
 *
 * @internal
 */
class FilterAndTest extends BaseCase
{
    /**
     * Проверяет, что объект вызовет все вложенные фильтры.
     */
    public function testTest(): void
    {
        $toTest = 'item to test';

        $filter1 = $this->getMockBuilder(Filter::class)->getMock();
        $filter1->expects($this->once())
            ->method('test')
            ->with($this->equalTo($toTest))
            ->willReturn(true);

        $filter2 = $this->getMockBuilder(Filter::class)->getMock();
        $filter2->expects($this->once())
            ->method('test')
            ->with($this->equalTo($toTest))
            ->willReturn(true);

        $filter = new FilterAnd([$filter1, $filter2]);
        $testResult = $filter->test($toTest);

        $this->assertTrue($testResult);
    }

    /**
     * Проверяет, что цепочка будет оборвана, если один из вложенных фильтров вернет false.
     */
    public function testNegativeTest(): void
    {
        $toTest = 'item to test';

        $filter1 = $this->getMockBuilder(Filter::class)->getMock();
        $filter1->expects($this->once())
            ->method('test')
            ->with($this->equalTo($toTest))
            ->willReturn(true);

        $filter2 = $this->getMockBuilder(Filter::class)->getMock();
        $filter2->expects($this->once())
            ->method('test')
            ->with($this->equalTo($toTest))
            ->willReturn(false);

        $filter3 = $this->getMockBuilder(Filter::class)->getMock();
        $filter3->expects($this->never())->method('test');

        $filter = new FilterAnd([$filter1, $filter2, $filter3]);
        $testResult = $filter->test($toTest);

        $this->assertFalse($testResult);
    }
}
