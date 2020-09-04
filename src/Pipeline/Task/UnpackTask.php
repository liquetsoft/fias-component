<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Psr\Log\LogLevel;
use SplFileInfo;

/**
 * Задача, которая распаковывает архив из файла в папку, указанные в состоянии.
 */
class UnpackTask implements Task, LoggableTask
{
    use LoggableTaskTrait;

    /**
     * @var Unpacker
     */
    protected $unpacker;

    /**
     * @param Unpacker $unpacker
     */
    public function __construct(Unpacker $unpacker)
    {
        $this->unpacker = $unpacker;
    }

    /**
     * @inheritDoc
     */
    public function run(State $state): void
    {
        $source = $state->getParameter(Task::DOWNLOAD_TO_FILE_PARAM);
        if (!($source instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter '" . Task::DOWNLOAD_TO_FILE_PARAM . "' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $destination = $state->getParameter(Task::EXTRACT_TO_FOLDER_PARAM);
        if (!($destination instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter '" . Task::EXTRACT_TO_FOLDER_PARAM . "' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $this->log(
            LogLevel::INFO,
            "Extracting '{$source->getRealPath()}' to '{$destination->getPathname()}'."
        );

        $this->unpacker->unpack($source, $destination);
    }
}
