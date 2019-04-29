<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Unpacker\Unpacker;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Exception\TaskException;
use SplFileInfo;

/**
 * Задача, которая распаковывает архив из файла в папку, указанные в состоянии.
 */
class UnpackTask implements Task
{
    /**
     * @var Unpacker
     */
    protected $unpacker;

    /**
     * @param Unpacker $downloader
     */
    public function __construct(Unpacker $unpacker)
    {
        $this->unpacker = $unpacker;
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $source = $state->getParameter('downloadTo');
        if (!($source instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter 'downloadTo' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $destination = $state->getParameter('unpackTo');
        if (!($destination instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter 'unpackTo' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $this->unpacker->unpack($source, $destination);
    }
}
