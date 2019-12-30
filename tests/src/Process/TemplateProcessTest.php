<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasInformer;

use Liquetsoft\Fias\Component\Process\TemplateProcess;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use Symfony\Component\Process\Process;

/**
 * Тест для объекта, который создает symfony process из шаблона команды.
 */
class TemplateProcessTest extends BaseCase
{
    /**
     * Проверяет, что подстановки сработают верно.
     */
    public function testCreateProcess()
    {
        $template = new TemplateProcess('command {{argument_1}} --option {{option_value}}');

        $process = $template->createProcess(['argument_1' => 'argument', 'option_value' => '/path/to']);

        $this->assertInstanceOf(Process::class, $process);
        $this->assertSame("'command' 'argument' '--option' '/path/to'", $process->getCommandLine());
    }
}
