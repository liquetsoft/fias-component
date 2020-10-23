<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Unpacker;

use Liquetsoft\Fias\Component\Exception\UnpackerException;
use Liquetsoft\Fias\Component\Process\TemplateProcess;
use SplFileInfo;
use Throwable;

/**
 * Объект, который распаковывает файлы из rar архива с помощью команды в консоли.
 *
 * Доступные подстановки:
 * - {{source}} путь к архиву,
 * - {{destination}} путь к папке, в которую следует распаковать файлы.
 */
class ProcessUnpacker implements Unpacker
{
    /**
     * @var TemplateProcess
     */
    protected $commandTemplate;

    /**
     * @param TemplateProcess $commandTemplate
     */
    public function __construct(TemplateProcess $commandTemplate)
    {
        $this->commandTemplate = $commandTemplate;
    }

    /**
     * @inheritdoc
     */
    public function unpack(SplFileInfo $source, SplFileInfo $destination, array $files_to_extract = []): void
    {
        try {
            $process = $this->commandTemplate->createProcess([
                'source' => $source->getPathname(),
                'destination' => $destination->getPathname(),
            ]);
            $process->setTimeout(null);
            $process->mustRun();
        } catch (Throwable $e) {
            $message = "Can't extract '{$source->getPathname()}' to '{$destination->getPathname()}'.";
            throw new UnpackerException($message, 0, $e);
        }
    }
}
